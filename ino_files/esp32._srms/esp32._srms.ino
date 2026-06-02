#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>

// ========== LCD Setup ==========
LiquidCrystal_I2C lcd(0x27, 16, 2);

// ========== Keypad Setup (4x3) ==========
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

// ========== Ultrasonic Sensor ==========
#define TRIG_PIN 32
#define ECHO_PIN 25

// ========== L298N Motor Driver ==========
#define ENA   12
#define IN1   26
#define IN2   27
#define IN3   33
#define IN4   13
#define ENB   2

// ========== IR Sensors ==========
#define IR_LEFT  34
#define IR_RIGHT 35

// ========== Motor Speeds ==========
const int LEFT_SPEED = 180;   // PWM 0-255
const int RIGHT_SPEED = 180;
const int TURN_SPEED = 120;

// ========== Time-Based Distances (milliseconds) ==========
// Adjust these values according to your track layout
const unsigned long TABLE_DURATION[] = {0, 5000, 10000, 15000, 20000, 25000};

// ========== System Variables ==========
int targetTable = 0;
bool isMovingToTable = false;
bool isReturning = false;
bool isWaitingConfirm = false;
bool isWaitingReturnConfirm = false;
bool isEmergencyStop = false;

unsigned long startTime = 0;
unsigned long returnStartTime = 0;

// ========== Setup ==========
void setup() {
  Serial.begin(115200);

  // Ultrasonic
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

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

  pinMode(IR_LEFT, INPUT);
  pinMode(IR_RIGHT, INPUT);

  stopMotors();

  displayMessage("Select Table:", "1-5 then #");
}

// ========== Helper Functions ==========
void displayMessage(String line1, String line2) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(line1);
  lcd.setCursor(0, 1);
  lcd.print(line2);
  Serial.println(line1 + " - " + line2);
}

// Ultrasonic distance (cm)
float getDistance() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  long duration = pulseIn(ECHO_PIN, HIGH, 30000); // timeout 30ms
  if (duration == 0) return 999;
  return duration * 0.034 / 2;
}

bool isObstacleDetected() {
  float d = getDistance();
  return (d > 0 && d < 20);
}

// Line following (adjust if your IR sensor logic is opposite)
void followLine() {
  int left = digitalRead(IR_LEFT);
  int right = digitalRead(IR_RIGHT);

  if (left == 0 && right == 0) {
    forward();
  } else if (left == 0 && right == 1) {
    leftTurn();
  } else if (left == 1 && right == 0) {
    rightTurn();
  } else {
    stopMotors(); // lost line
  }
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

// Emergency stop – press any key to resume
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
      displayMessage("Select Table:", "1-5 then #");
      break;
    }
    delay(100);
  }
}

// ========== Main Loop ==========
void loop() {
  char key = keypad.getKey();

  if (key) {
    Serial.print("Key: ");
    Serial.println(key);

    if (key == '*') {
      emergencyStop();
      return;   // skip rest of loop
    }

    if (!isEmergencyStop) {
      // Waiting for table selection
      if (!isMovingToTable && !isReturning && !isWaitingConfirm && !isWaitingReturnConfirm) {
        if (key >= '1' && key <= '5') {
          targetTable = key - '0';
          displayMessage("Table " + String(targetTable), "Press # to confirm");
          isWaitingConfirm = true;
        }
      }
      // Confirm table selection
      else if (isWaitingConfirm && key == '#' && targetTable > 0) {
        isWaitingConfirm = false;
        isMovingToTable = true;
        startTime = millis();
        displayMessage("Going to Table", String(targetTable));
        delay(1000);
      }
      // Wait for return confirmation after arrival
      else if (isWaitingReturnConfirm && key == '#') {
        isWaitingReturnConfirm = false;
        isReturning = true;
        returnStartTime = millis();
        displayMessage("Returning to", "Start");
        delay(1000);
      }
    }
  }

  // ----- Moving to Table -----
  if (isMovingToTable && !isEmergencyStop) {
    // Obstacle handling
    if (isObstacleDetected()) {
      stopMotors();
      displayMessage("OBSTACLE!", "Remove & continue");
      while (isObstacleDetected() && !isEmergencyStop) {
        delay(500);
        char k = keypad.getKey();
        if (k == '*') emergencyStop();
      }
      if (!isEmergencyStop) {
        displayMessage("Resuming...", "");
        delay(1000);
      }
    }

    followLine();   // line following while moving

    // Time-based arrival
    if (millis() - startTime >= TABLE_DURATION[targetTable]) {
      stopMotors();
      isMovingToTable = false;
      isWaitingReturnConfirm = true;
      displayMessage("Table arrived!", "Press # to return");
    }
  }

  // ----- Returning to Start -----
  if (isReturning && !isEmergencyStop) {
    if (isObstacleDetected()) {
      stopMotors();
      displayMessage("OBSTACLE!", "Remove & continue");
      while (isObstacleDetected() && !isEmergencyStop) {
        delay(500);
        char k = keypad.getKey();
        if (k == '*') emergencyStop();
      }
      if (!isEmergencyStop) {
        displayMessage("Resuming...", "");
        delay(1000);
      }
    }

    followLine();

    if (millis() - returnStartTime >= TABLE_DURATION[targetTable]) {
      stopMotors();
      isReturning = false;
      targetTable = 0;
      isWaitingReturnConfirm = false;
      displayMessage("Back to Start", "Ready for next");
    }
  }

  delay(50);
}