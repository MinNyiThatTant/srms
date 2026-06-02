#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>
#include <DFRobotDFPlayerMini.h>

// ========== LCD I2C (SDA=21, SCL=22) ==========
LiquidCrystal_I2C lcd(0x27, 16, 2);

// ========== Keypad 4x3 (rows: 18,19,2,15 ; cols: 23,4,5) ==========
const byte ROWS = 4;
const byte COLS = 3;
char keys[ROWS][COLS] = {
  {'1','2','3'},
  {'4','5','6'},
  {'7','8','9'},
  {'*','0','#'}
};
byte rowPins[ROWS] = {18, 19, 2, 15};
byte colPins[COLS] = {23, 4, 5};
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);


DFRobotDFPlayerMini myDFPlayer;

#define TRIG_PIN 32
#define ECHO_PIN 33

#define BUZZER_PIN 0

#define IN1 26
#define IN2 27
#define IN3 14
#define IN4 12


#define IR_LEFT  34
#define IR_RIGHT 35


const unsigned long TABLE_TIMES[] = {0, 5000, 10000, 15000, 20000, 25000};


int targetTable = 0;
bool isMovingToTable = false;
bool isReturning = false;
bool isWaitingConfirm = false;
bool isWaitingReturnConfirm = false;
bool isEmergencyStop = false;
bool isPausedForObstacle = false;

unsigned long travelStartTime = 0;
unsigned long travelElapsedBeforePause = 0;
unsigned long travelTargetDuration = 0;

// ========== SETUP ==========
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
  
  // DFPlayer Mini
  Serial2.begin(9600, SERIAL_8N1, 16, 17);
  delay(100);
  if (!myDFPlayer.begin(Serial2)) {
    Serial.println("DFPlayer not detected!");
    lcd.setCursor(0, 1);
    lcd.print("No DFPlayer");
  } else {
    myDFPlayer.volume(25);
    Serial.println("DFPlayer ready!");
  }
  
  // Buzzer
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  
  // Ultrasonic
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  
  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);
  
  // Line sensors
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

void beep() {
  tone(BUZZER_PIN, 1000, 100);
  delay(100);
  noTone(BUZZER_PIN);
}

void playConfirmSound() {
  tone(BUZZER_PIN, 1200, 150);
  delay(200);
  tone(BUZZER_PIN, 1500, 150);
  delay(200);
  noTone(BUZZER_PIN);
}

void playArrivalSound() {
  for (int i = 0; i < 3; i++) {
    tone(BUZZER_PIN, 2000, 200);
    delay(250);
  }
  noTone(BUZZER_PIN);
}

void playReturnSound() {
  tone(BUZZER_PIN, 800, 500);
  delay(500);
  noTone(BUZZER_PIN);
}

void playObstacleSound() {
  for (int i = 0; i < 2; i++) {
    tone(BUZZER_PIN, 800, 100);
    delay(150);
  }
  noTone(BUZZER_PIN);
}

void playEmergencySound() {
  for (int i = 0; i < 5; i++) {
    tone(BUZZER_PIN, 500, 200);
    delay(250);
  }
  noTone(BUZZER_PIN);
}

// ========== Ultrasonic Distance ==========
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
  return (d < 20 && d > 0);
}

// ========== Line Following (Black = LOW, White = HIGH) ==========
void followLine() {
  int left = digitalRead(IR_LEFT);
  int right = digitalRead(IR_RIGHT);
  
  if (left == 1 && right == 1) forward();
  else if (left == 0 && right == 1) rightTurn();
  else if (left == 1 && right == 0) leftTurn();
  else stopMotors();   // both white – stop at table or start
}

// ========== Motor Control (NO ENA/ENB – rely on hardware jumpers) ==========
void forward() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, HIGH);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, HIGH);
}

void leftTurn() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, HIGH);
  digitalWrite(IN3, HIGH);
  digitalWrite(IN4, LOW);
}

void rightTurn() {
  digitalWrite(IN1, HIGH);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, HIGH);
}

void stopMotors() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, LOW);
}

// ========== Emergency Stop ==========
void emergencyStop() {
  isEmergencyStop = true;
  isMovingToTable = false;
  isReturning = false;
  isWaitingConfirm = false;
  isWaitingReturnConfirm = false;
  isPausedForObstacle = false;
  targetTable = 0;
  stopMotors();
  playEmergencySound();
  displayMessage("EMERGENCY STOP!", "Press any key");
  
  while (true) {
    char key = keypad.getKey();
    if (key) {
      beep();
      isEmergencyStop = false;
      displayMessage("Select Table:", "1-5 then #");
      break;
    }
    delay(100);
  }
}

// ========== MAIN LOOP (Time‑Based Travel) ==========
void loop() {
  char key = keypad.getKey();
  if (key) {
    beep();
    if (key == '*') {
      emergencyStop();
      return;
    }
    
    if (!isEmergencyStop) {
      // Select table (1-5)
      if (key >= '1' && key <= '5' && !isMovingToTable && !isReturning && !isWaitingConfirm && !isWaitingReturnConfirm) {
        targetTable = key - '0';
        displayMessage("Table " + String(targetTable), "Press # to confirm");
        isWaitingConfirm = true;
      }
      else if (isWaitingConfirm && key == '#' && targetTable > 0) {
        playConfirmSound();
        displayMessage("Going to Table", String(targetTable));
        delay(1000);
        isMovingToTable = true;
        isWaitingConfirm = false;
        travelTargetDuration = TABLE_TIMES[targetTable];
        travelStartTime = millis();
        travelElapsedBeforePause = 0;
        isPausedForObstacle = false;
      }
      else if (isWaitingReturnConfirm && key == '#') {
        playConfirmSound();
        displayMessage("Returning to", "Start");
        delay(1000);
        isReturning = true;
        isWaitingReturnConfirm = false;
        travelTargetDuration = TABLE_TIMES[targetTable];
        travelStartTime = millis();
        travelElapsedBeforePause = 0;
        isPausedForObstacle = false;
      }
    }
  }
  
  // ---------- Moving to Table ----------
  if (isMovingToTable && !isEmergencyStop) {
    if (isObstacleDetected()) {
      if (!isPausedForObstacle) {
        travelElapsedBeforePause = millis() - travelStartTime;
        stopMotors();
        playObstacleSound();
        displayMessage("OBSTACLE!", "Remove and continue");
        isPausedForObstacle = true;
      }
      while (isObstacleDetected() && !isEmergencyStop) {
        delay(500);
        char k = keypad.getKey();
        if (k == '*') emergencyStop();
      }
      if (!isEmergencyStop && isPausedForObstacle) {
        playConfirmSound();
        displayMessage("Resuming...", "");
        delay(1000);
        travelStartTime = millis() - travelElapsedBeforePause;
        isPausedForObstacle = false;
      }
    } else {
      followLine();
    }
    
    if (!isPausedForObstacle) {
      if (millis() - travelStartTime >= travelTargetDuration) {
        stopMotors();
        isMovingToTable = false;
        isWaitingReturnConfirm = true;
        displayMessage("Table arrived!", "Press # to return");
        playArrivalSound();
      }
    }
  }
  
  // ---------- Returning to Start ----------
  if (isReturning && !isEmergencyStop) {
    if (isObstacleDetected()) {
      if (!isPausedForObstacle) {
        travelElapsedBeforePause = millis() - travelStartTime;
        stopMotors();
        playObstacleSound();
        displayMessage("OBSTACLE!", "Remove and continue");
        isPausedForObstacle = true;
      }
      while (isObstacleDetected() && !isEmergencyStop) {
        delay(500);
        char k = keypad.getKey();
        if (k == '*') emergencyStop();
      }
      if (!isEmergencyStop && isPausedForObstacle) {
        playConfirmSound();
        displayMessage("Resuming...", "");
        delay(1000);
        travelStartTime = millis() - travelElapsedBeforePause;
        isPausedForObstacle = false;
      }
    } else {
      followLine();
    }
    
    if (!isPausedForObstacle) {
      if (millis() - travelStartTime >= travelTargetDuration) {
        stopMotors();
        isReturning = false;
        targetTable = 0;
        isWaitingReturnConfirm = false;
        displayMessage("Back to Start", "Ready for next");
        playReturnSound();
      }
    }
  }
  
  delay(50);
}