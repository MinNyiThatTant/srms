#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>

// lcd
LiquidCrystal_I2C lcd(0x27, 16, 2);

// keypad
const byte ROWS = 4;
const byte COLS = 3;
char keys[ROWS][COLS] = {
  {'1','2','3'},
  {'4','5','6'},
  {'7','8','9'},
  {'*','0','#'}
};
byte rowPins[ROWS] = {18, 19, 5, 17};    // Row 1,2,3,4
byte colPins[COLS] = {23, 4, 16};         // Col 1,2,3
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

// motor pins
#define ENA 25
#define IN1 26
#define IN2 27
#define IN3 32
#define IN4 33
#define ENB 13

// IR sensor pins
#define IR_LEFT 34
#define IR_RIGHT 35

// Motor speeds
const int LEFT_SPEED = 180;
const int RIGHT_SPEED = 180;
const int TURN_SPEED = 120;

int targetTable = 0;
bool isMovingToTable = false;
bool isReturning = false;
bool isWaitingConfirm = false;

// time(ms) to reach each table (1-5)
const int TABLE_DISTANCE_MS[] = {0, 0, 4000, 8000, 12000, 16000};


void setup() {
  Serial.begin(115200);
  
  // LCD
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Restaurant Robot");
  lcd.setCursor(0, 1);
  lcd.print("System Ready");
  delay(2000);
  lcd.clear();
  
  // Motor pins
  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);
  pinMode(ENA, OUTPUT);
  pinMode(ENB, OUTPUT);
  
  // IR Sensor pins
  pinMode(IR_LEFT, INPUT);
  pinMode(IR_RIGHT, INPUT);
  
  stopMotors();
  
  displayMessage("Select Table:", "1-5 then #");
}

void displayMessage(String line1, String line2) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(line1);
  lcd.setCursor(0, 1);
  lcd.print(line2);
}

void loop() {
  char key = keypad.getKey();
  
  if (key && !isMovingToTable && !isReturning && !isWaitingConfirm) {
    if (key >= '1' && key <= '5') {
      targetTable = key - '0';
      displayMessage("Table " + String(targetTable), "Press # to confirm");
      isWaitingConfirm = true;
    }
    else if (key == '*') {
      stopMotors();
      isMovingToTable = false;
      isReturning = false;
      isWaitingConfirm = false;
      targetTable = 0;
      displayMessage("Emergency Stop", "Press 1-5 to start");
    }
  }
  
  if (isWaitingConfirm && key == '#' && targetTable > 0) {
    displayMessage("Going to Table", String(targetTable));
    delay(1000);
    isMovingToTable = true;
    isWaitingConfirm = false;
  }
  
  if (isMovingToTable && !isReturning) {
    int left = digitalRead(IR_LEFT);
    int right = digitalRead(IR_RIGHT);
    
    if (left == 0 && right == 0) forward();
    else if (left == 0 && right == 1) leftTurn();
    else if (left == 1 && right == 0) rightTurn();
    else stopMotors();
    
    static unsigned long startTime = 0;
    if (startTime == 0) startTime = millis();
    
    if (millis() - startTime >= TABLE_DISTANCE_MS[targetTable]) {
      stopMotors();
      isMovingToTable = false;
      isWaitingConfirm = true;
      displayMessage("Table " + String(targetTable), "Reached! Press #");
      startTime = 0;
    }
  }
  
  if (isWaitingConfirm && !isMovingToTable && !isReturning && targetTable > 0 && key == '#') {
    displayMessage("Returning to", "Start");
    delay(1000);
    isReturning = true;
    isWaitingConfirm = false;
  }
  
  if (isReturning && !isMovingToTable) {
    int left = digitalRead(IR_LEFT);
    int right = digitalRead(IR_RIGHT);
    
    if (left == 0 && right == 0) forward();
    else if (left == 0 && right == 1) leftTurn();
    else if (left == 1 && right == 0) rightTurn();
    else stopMotors();
    
    static unsigned long returnStartTime = 0;
    if (returnStartTime == 0) returnStartTime = millis();
    
    if (millis() - returnStartTime >= TABLE_DISTANCE_MS[targetTable]) {
      stopMotors();
      isReturning = false;
      targetTable = 0;
      displayMessage("Back to Start", "Ready for next");
      returnStartTime = 0;
    }
  }
  
  delay(50);
}

void forward() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, HIGH);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, HIGH);
  analogWrite(ENA, LEFT_SPEED);
  analogWrite(ENB, RIGHT_SPEED);
}

void leftTurn() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, HIGH);
  digitalWrite(IN3, HIGH);
  digitalWrite(IN4, LOW);
  analogWrite(ENA, TURN_SPEED);
  analogWrite(ENB, TURN_SPEED);
}

void rightTurn() {
  digitalWrite(IN1, HIGH);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, HIGH);
  analogWrite(ENA, TURN_SPEED);
  analogWrite(ENB, TURN_SPEED);
}

void stopMotors() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, LOW);
  analogWrite(ENA, 0);
  analogWrite(ENB, 0);
}