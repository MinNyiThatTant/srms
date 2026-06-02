// 4WD Motor Test Code for ESP32 + L298N
// မော်တာ ၄ လုံးစလုံးကို တစ်ပြိုင်နက် စမ်းသပ်မည်

#define ENA 12
#define IN1 26
#define IN2 27
#define IN3 33
#define IN4 13
#define ENB 2

// အမြန်နှုန်း (0-255)
int speedVal = 200;

void setup() {
  Serial.begin(115200);
  
  pinMode(ENA, OUTPUT);
  pinMode(ENB, OUTPUT);
  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);
  
  // Enable motor drivers (PWM)
  analogWrite(ENA, speedVal);
  analogWrite(ENB, speedVal);
  
  Serial.println("4WD Motor Test Started");
}

void loop() {
  // 1. ရှေ့သို့ (Forward) - 2 sec
  Serial.println("Forward");
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, HIGH);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, HIGH);
  delay(2000);
  
  // 2. ရပ်ရန် (Stop) - 1 sec
  Serial.println("Stop");
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, LOW);
  delay(1000);
  
  // 3. နောက်သို့ (Backward) - 2 sec
  Serial.println("Backward");
  digitalWrite(IN1, HIGH);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, HIGH);
  digitalWrite(IN4, LOW);
  delay(2000);
  
  // 4. ရပ်ရန် (Stop) - 1 sec
  Serial.println("Stop");
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, LOW);
  delay(1000);
  
  // 5. ဘယ်သို့အကွေ့ (Left turn) - 1.5 sec
  Serial.println("Left Turn");
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, HIGH);   // ဘယ်ဘက် မော်တာများ ရှေ့
  digitalWrite(IN3, HIGH);
  digitalWrite(IN4, LOW);    // ညာဘက် မော်တာများ နောက်
  delay(1500);
  
  // 6. ရပ်ရန် (Stop)
  Serial.println("Stop");
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, LOW);
  delay(1000);
  
  // 7. ညာသို့အကွေ့ (Right turn) - 1.5 sec
  Serial.println("Right Turn");
  digitalWrite(IN1, HIGH);
  digitalWrite(IN2, LOW);    // ဘယ်ဘက် မော်တာများ နောက်
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, HIGH);   // ညာဘက် မော်တာများ ရှေ့
  delay(1500);
  
  // 8. ရပ်ရန် (Stop)
  Serial.println("Stop");
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, LOW);
  delay(1000);
  
  Serial.println("Cycle complete, restarting...");
  delay(500);
}