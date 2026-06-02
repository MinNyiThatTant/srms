#include <Keypad.h>

#define ENA   21
#define ENB   22
#define IN1   26
#define IN2   27
#define IN3   33
#define IN4   13
#define IR_LEFT   34
#define IR_RIGHT  35
#define TRIG  32
#define ECHO  25
#define DF_RX 16
#define DF_TX 17

const byte ROWS = 4; const byte COLS = 3;
char keys[ROWS][COLS] = {{'1','2','3'},{'4','5','6'},{'7','8','9'},{'*','0','#'}};
byte rowPins[ROWS] = {18, 19, 5, 23};
byte colPins[COLS] = {4, 15, 14};
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

const int MIN_SPEED = 150;
const int BASE_SPEED = 180;
const int CURVE_INNER = 150;
const int CURVE_OUTER = 255;
const int PIVOT_SPEED = 180;

const int TOTAL_MARKERS = 5;
const int OBSTACLE_DISTANCE = 15;

#define AUDIO_READY        1
#define AUDIO_GO_TABLE1    2
#define AUDIO_GO_TABLE2    3
#define AUDIO_GO_TABLE3    4
#define AUDIO_GO_TABLE4    5
#define AUDIO_PRESS_KEY    6
#define AUDIO_LEAVE_TABLE1 7
#define AUDIO_LEAVE_TABLE2 8
#define AUDIO_LEAVE_TABLE3 9
#define AUDIO_LEAVE_TABLE4 10
#define AUDIO_HOME         11

enum State { STATE_BOOT, STATE_WAIT_KEY, STATE_MOVING_TO_TABLE, STATE_AT_TABLE, STATE_RETURNING };
State currentState = STATE_BOOT;

int targetTable = 0;
int currentMarker = 0;
int lastDirection = 0;
bool inCurve = false;
unsigned long lastUltrasonicCheck = 0;
bool usePivotTurn = true;

// ----- MARKER DETECTION (transition based + cooldown) -----
bool onMarker = false;
unsigned long lastMarkerTime = 0;
const unsigned long MARKER_COOLDOWN = 400;

void setup() {
  Serial.begin(115200);
  pinMode(ENA, OUTPUT); pinMode(ENB, OUTPUT);
  pinMode(IN1, OUTPUT); pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT); pinMode(IN4, OUTPUT);
  pinMode(IR_LEFT, INPUT); pinMode(IR_RIGHT, INPUT);
  pinMode(TRIG, OUTPUT); pinMode(ECHO, INPUT);
  
  ledcSetup(0, 10000, 8);
  ledcSetup(1, 10000, 8);
  ledcAttachPin(ENA, 0);
  ledcAttachPin(ENB, 1);
  
  Serial2.begin(9600, SERIAL_8N1, DF_RX, DF_TX);
  delay(1000);
  
  setForwardDirection();
  
  Serial.println("Booting...");
  playAudio(AUDIO_READY);
  delay(2000);
  
  currentState = STATE_WAIT_KEY;
  Serial.println("Ready. Press 1-4");
}

void loop() {
  char key = keypad.getKey();
  if (key) handleKeypad(key);
  
  switch (currentState) {
    case STATE_MOVING_TO_TABLE: followLineToTarget(); checkObstacle(); break;
    case STATE_RETURNING: followLineReturn(); checkObstacle(); break;
    default: break;
  }
  delay(5);
}

// =====================================================
// KEYPAD
// =====================================================
void handleKeypad(char key) {
  Serial.print("Key: "); Serial.println(key);
  
  if (currentState == STATE_WAIT_KEY && key >= '1' && key <= '4') {
    targetTable = key - '0';
    currentMarker = 0;
    resetState();
    playAudio(AUDIO_GO_TABLE1 + targetTable - 1);
    delay(1500);
    currentState = STATE_MOVING_TO_TABLE;
  }
  else if (currentState == STATE_AT_TABLE && key == '#') {
    playAudio(AUDIO_LEAVE_TABLE1 + targetTable - 1);
    delay(1500);
    currentState = STATE_RETURNING;
    resetState();
  }
  else if (currentState == STATE_WAIT_KEY && key == '#') {
    playAudio(AUDIO_PRESS_KEY);
  }
}

void resetState() {
  lastDirection = 0;
  inCurve = false;
  onMarker = false;
  lastMarkerTime = 0;
}

// =====================================================
// LINE FOLLOWING TO TABLE
// =====================================================
void followLineToTarget() {
  int rawLeft = digitalRead(IR_LEFT);    // Black=1, White=0
  int rawRight = digitalRead(IR_RIGHT);
  
  static unsigned long lastPrint = 0;
  if (millis() - lastPrint > 400) {
    lastPrint = millis();
    Serial.print("GO L="); Serial.print(rawLeft);
    Serial.print(" R="); Serial.print(rawRight);
    Serial.print(" M="); Serial.println(currentMarker);
  }
  
  // ----- MARKER DETECTION -----
  if (millis() - lastMarkerTime > MARKER_COOLDOWN) {
    if (!onMarker && rawLeft == 0 && rawRight == 0) {
      onMarker = true;
      Serial.println("Marker white");
    }
    else if (onMarker && rawLeft == 1 && rawRight == 1) {
      onMarker = false;
      lastMarkerTime = millis();
      currentMarker++;
      Serial.print("*** MARKER "); Serial.println(currentMarker);
      
      if (currentMarker == targetTable) {
        Serial.println("ARRIVED! Prompting customer...");
        stopMotors();
        playAudio(AUDIO_PRESS_KEY);
        delay(2000);
        currentState = STATE_AT_TABLE;
        return;
      }
    }
  }
  
  // ----- LINE FOLLOW LOGIC -----
  if (rawLeft == 1 && rawRight == 1) {
    lastDirection = 0;
    inCurve = false;
    setForwardDirection();
    moveForward(BASE_SPEED, BASE_SPEED);
  }
  else if (rawLeft == 1 && rawRight == 0) {
    lastDirection = -1;
    inCurve = true;
    if (usePivotTurn) pivotLeft();
    else { setForwardDirection(); moveForward(CURVE_INNER, CURVE_OUTER); }
  }
  else if (rawLeft == 0 && rawRight == 1) {
    lastDirection = 1;
    inCurve = true;
    if (usePivotTurn) pivotRight();
    else { setForwardDirection(); moveForward(CURVE_OUTER, CURVE_INNER); }
  }
  else {
    // Both white (on marker strip) - go straight across
    setForwardDirection();
    moveForward(BASE_SPEED, BASE_SPEED);
  }
}

// =====================================================
// RETURNING HOME
// =====================================================
void followLineReturn() {
  int rawLeft = digitalRead(IR_LEFT);
  int rawRight = digitalRead(IR_RIGHT);
  
  static unsigned long lastPrint = 0;
  if (millis() - lastPrint > 400) {
    lastPrint = millis();
    Serial.print("RET L="); Serial.print(rawLeft);
    Serial.print(" R="); Serial.print(rawRight);
    Serial.print(" M="); Serial.println(currentMarker);
  }
  
  // Marker detection
  if (millis() - lastMarkerTime > MARKER_COOLDOWN) {
    if (!onMarker && rawLeft == 0 && rawRight == 0) {
      onMarker = true;
      Serial.println("Marker white (ret)");
    }
    else if (onMarker && rawLeft == 1 && rawRight == 1) {
      onMarker = false;
      lastMarkerTime = millis();
      currentMarker++;
      Serial.print("*** MARKER "); Serial.print(currentMarker); Serial.println(" (return)");
      
      if (currentMarker >= TOTAL_MARKERS) {
        Serial.println("HOME!");
        stopMotors();
        playAudio(AUDIO_HOME);
        delay(2000);
        currentState = STATE_WAIT_KEY;
        return;
      }
    }
  }
  
  if (rawLeft == 1 && rawRight == 1) {
    lastDirection = 0;
    inCurve = false;
    setForwardDirection();
    moveForward(BASE_SPEED, BASE_SPEED);
  }
  else if (rawLeft == 1 && rawRight == 0) {
    lastDirection = -1;
    inCurve = true;
    if (usePivotTurn) pivotLeft();
    else { setForwardDirection(); moveForward(CURVE_INNER, CURVE_OUTER); }
  }
  else if (rawLeft == 0 && rawRight == 1) {
    lastDirection = 1;
    inCurve = true;
    if (usePivotTurn) pivotRight();
    else { setForwardDirection(); moveForward(CURVE_OUTER, CURVE_INNER); }
  }
  else {
    setForwardDirection();
    moveForward(BASE_SPEED, BASE_SPEED);
  }
}

// =====================================================
// PIVOT TURNS
// =====================================================
void pivotLeft() {
  digitalWrite(IN1, LOW);   digitalWrite(IN2, HIGH);
  digitalWrite(IN3, HIGH);  digitalWrite(IN4, LOW);
  ledcWrite(0, PIVOT_SPEED);
  ledcWrite(1, PIVOT_SPEED);
}

void pivotRight() {
  digitalWrite(IN1, HIGH);  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);   digitalWrite(IN4, HIGH);
  ledcWrite(0, PIVOT_SPEED);
  ledcWrite(1, PIVOT_SPEED);
}

void continueCurve() {
  if (lastDirection == -1) {
    if (usePivotTurn) pivotLeft();
    else { setForwardDirection(); moveForward(CURVE_INNER, CURVE_OUTER); }
  }
  else if (lastDirection == 1) {
    if (usePivotTurn) pivotRight();
    else { setForwardDirection(); moveForward(CURVE_OUTER, CURVE_INNER); }
  }
  else {
    setForwardDirection();
    moveForward(BASE_SPEED, BASE_SPEED);
  }
}

void handleLostLine() {
  Serial.println("Searching...");
  if (lastDirection == 1) {
    if (usePivotTurn) pivotRight();
    else { setForwardDirection(); moveForward(CURVE_OUTER, CURVE_INNER); }
  }
  else if (lastDirection == -1) {
    if (usePivotTurn) pivotLeft();
    else { setForwardDirection(); moveForward(CURVE_INNER, CURVE_OUTER); }
  }
  else {
    moveForward(-BASE_SPEED, BASE_SPEED);
  }
}

// =====================================================
// OBSTACLE
// =====================================================
void checkObstacle() {
  if (millis() - lastUltrasonicCheck < 100) return;
  lastUltrasonicCheck = millis();
  long dist = getDistance();
  if (dist > 0 && dist < OBSTACLE_DISTANCE) {
    Serial.print("Obstacle: "); Serial.println(dist);
    stopMotors();
    while (getDistance() < OBSTACLE_DISTANCE) delay(100);
    Serial.println("Clear");
  }
}

long getDistance() {
  digitalWrite(TRIG, LOW); delayMicroseconds(2);
  digitalWrite(TRIG, HIGH); delayMicroseconds(10);
  digitalWrite(TRIG, LOW);
  long d = pulseIn(ECHO, HIGH, 30000);
  return (d == 0) ? -1 : d * 0.034 / 2;
}

// =====================================================
// AUDIO
// =====================================================
void playAudio(int file) {
  Serial.print("Play: "); Serial.println(file);
  uint8_t cmd[] = {0x7E, 0xFF, 0x06, 0x03, 0x00, 0x00, (uint8_t)file, 0xEF};
  Serial2.write(cmd, 8);
}

// =====================================================
// MOTOR CONTROL
// =====================================================
void setForwardDirection() {
  digitalWrite(IN1, LOW);  digitalWrite(IN2, HIGH);
  digitalWrite(IN3, LOW);  digitalWrite(IN4, HIGH);
}

void moveForward(int leftSpeed, int rightSpeed) {
  leftSpeed = constrain(leftSpeed, 0, 255);
  rightSpeed = constrain(rightSpeed, 0, 255);
  if (leftSpeed > 0 && leftSpeed < MIN_SPEED) leftSpeed = MIN_SPEED;
  if (rightSpeed > 0 && rightSpeed < MIN_SPEED) rightSpeed = MIN_SPEED;
  ledcWrite(1, leftSpeed);
  ledcWrite(0, rightSpeed);
}

void stopMotors() {
  ledcWrite(0, 0);
  ledcWrite(1, 0);
}