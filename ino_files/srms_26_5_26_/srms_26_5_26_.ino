void setup() {
  pinMode(26, OUTPUT);
  pinMode(27, OUTPUT);
  pinMode(14, OUTPUT);
  pinMode(12, OUTPUT);
  // No need to touch ENA/ENB because jumpers are installed
  
  digitalWrite(26, LOW);
  digitalWrite(27, HIGH);   // Motor A forward
  digitalWrite(14, LOW);
  digitalWrite(12, HIGH);   // Motor B forward
}

void loop() {}