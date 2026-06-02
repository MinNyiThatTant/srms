#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>
#include <HardwareSerial.h>

// ========== LCD Setup ==========
LiquidCrystal_I2C lcd(0x27, 16, 2);

// ========== Keypad Setup (4x3) – table 1-4 only ==========
const byte ROWS = 4;
const byte COLS = 3;
char keys[ROWS][COLS] = {
  {'1','2','3'},
  {'4','5','6'},
  {'7','8','9'},
  {'*','0','#'}
};
byte rowPins[ROWS] = {18, 19, 5, 23};
byte colPins[COLS] = {4, 15, 14};
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

// ========== DFPlayer (commented) ==========
// HardwareSerial mySerial(2);
// DFRobotDFPlayerMini myDFPlayer;

// ========== Ultrasonic Sensor ==========
#define TRIG_PIN 32
#define ECHO_PIN 25

// ========== L298N Motor Driver (Jumpers MUST be removed) ==========
#define ENA   12
#define IN1   26
#define IN2   27
#define IN3   33
#define IN4   13
#define ENB   2

// ========== IR Sensors (FRONT) ==========
#define IR_LEFT  34
#define IR_RIGHT 35

// ========== Motor Speeds ==========
const int LEFT_SPEED = 90;
const int RIGHT_SPEED = 90;
const int TURN_SPEED = 75;

// ========== Marker Detection Parameters ==========
const unsigned long MARKER_MIN_DURATION = 40;
const unsigned long MARKER_COOLDOWN = 250;

// ========== System Variables ==========
int targetTable = 0;
bool isMovingToTable = false;
bool isReturning = false;
bool isWaitingConfirm = false;
bool isWaitingReturnConfirm = false;
bool isEmergencyStop = false;

// ========== Marker Counting ==========
int markerCount = 0;
bool lastWasWhite = false;
unsigned long whiteStartTime = 0;
unsigned long lastMarkerTime = 0;

// ========== Setup ==========
void setup() {
  Serial.begin(115200);
  
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  
  lcd.init();
  lcd.backlight();
  lcd.print("Restaurant Robot");
  lcd.setCursor(0,1);
  lcd.print("Marker Ready");
  delay(2000);
  lcd.clear();
  
  pinMode(ENA, OUTPUT);
  pinMode(ENB, OUTPUT);
  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);
  
  pinMode(IR_LEFT, INPUT);
  pinMode(IR_RIGHT, INPUT);
  
  stopMotors();
  displayMessage("Select Table:", "1-4 then #");
}

void displayMessage(String line1, String line2) {
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print(line1);
  lcd.setCursor(0,1);
  lcd.print(line2);
  Serial.println(line1 + " - " + line2);
}

float getDistance() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  long duration = pulseIn(ECHO_PIN, HIGH, 30000);
  if (duration == 0) return 999;
  return duration * 0.034 / 2;
}

bool isObstacleDetected() {
  float d = getDistance();
  return (d > 0 && d < 20);
}

// ---- Line Following (ပြောင်းပြန် - Black=1, White=0) ----
void followLine() {
  int left = digitalRead(IR_LEFT);
  int right = digitalRead(IR_RIGHT);
  
  if (left == 1 && right == 1) forward();       // both black → forward
  else if (left == 1 && right == 0) leftTurn();  // left black, right white → leftTurn
  else if (left == 0 && right == 1) rightTurn(); // right black, left white → rightTurn
  else stopMotors();                             // both white → stop
}

// ---- Marker Detection (white line = 0,0) ----
void checkAndCountMarker(int direction) {
  int left = digitalRead(IR_LEFT);
  int right = digitalRead(IR_RIGHT);
  bool bothWhite = (left == 0 && right == 0);   // marker condition (white line)
  
  if (bothWhite && !lastWasWhite) {
    whiteStartTime = millis();
    lastWasWhite = true;
  }
  else if (!bothWhite && lastWasWhite) {
    lastWasWhite = false;
    unsigned long duration = millis() - whiteStartTime;
    if (duration >= MARKER_MIN_DURATION && 
        (millis() - lastMarkerTime) >= MARKER_COOLDOWN) {
      markerCount += direction;
      lastMarkerTime = millis();
      Serial.print("Marker! Count = ");
      Serial.println(markerCount);
    }
  }
}

// ---- Motor Control ----
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

void emergencyStop() {
  isEmergencyStop = true;
  isMovingToTable = false;
  isReturning = false;
  isWaitingConfirm = false;
  isWaitingReturnConfirm = false;
  targetTable = 0;
  stopMotors();
  displayMessage("EMERGENCY STOP!", "Press any key");
  while (true) {
    char key = keypad.getKey();
    if (key) {
      isEmergencyStop = false;
      displayMessage("Select Table:", "1-4 then #");
      break;
    }
    delay(100);
  }
}

// ========== Main Loop ==========
void loop() {
  char key = keypad.getKey();
  
  if (key) {
    if (key == '*') {
      emergencyStop();
      return;
    }
    
    if (!isEmergencyStop) {
      if (!isMovingToTable && !isReturning && !isWaitingConfirm && !isWaitingReturnConfirm) {
        if (key >= '1' && key <= '4') {
          targetTable = key - '0';
          displayMessage("Table " + String(targetTable), "Press # to confirm");
          isWaitingConfirm = true;
        }
      }
      else if (isWaitingConfirm && key == '#' && targetTable > 0) {
        isWaitingConfirm = false;
        isMovingToTable = true;
        markerCount = 0;
        lastWasWhite = false;
        lastMarkerTime = 0;
        displayMessage("Going to Table", String(targetTable));
        delay(1000);
      }
      else if (isWaitingReturnConfirm && key == '#') {
        isWaitingReturnConfirm = false;
        isReturning = true;
        displayMessage("Returning to", "Start");
        delay(1000);
      }
    }
  }
  
  // Forward Movement (increase marker count)
  if (isMovingToTable && !isEmergencyStop) {
    if (isObstacleDetected()) {
      stopMotors();
      displayMessage("OBSTACLE!", "Remove & continue");
      while (isObstacleDetected() && !isEmergencyStop) delay(500);
      if (!isEmergencyStop) displayMessage("Resuming...", "");
      delay(1000);
    }
    followLine();
    checkAndCountMarker(+1);
    
    if (markerCount >= targetTable) {
      stopMotors();
      isMovingToTable = false;
      isWaitingReturnConfirm = true;
      displayMessage("Table arrived!", "Press # to return");
    }
  }
  
  // Return Movement (decrease marker count)
  if (isReturning && !isEmergencyStop) {
    if (isObstacleDetected()) {
      stopMotors();
      displayMessage("OBSTACLE!", "Remove & continue");
      while (isObstacleDetected() && !isEmergencyStop) delay(500);
      if (!isEmergencyStop) displayMessage("Resuming...", "");
      delay(1000);
    }
    followLine();
    checkAndCountMarker(-1);
    
    if (markerCount <= 0) {
      stopMotors();
      isReturning = false;
      targetTable = 0;
      isWaitingReturnConfirm = false;
      displayMessage("Back to Start", "Ready for next");
    }
  }
  
  delay(50);
}