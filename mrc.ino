#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 15
#define RST_PIN 0
#define BUZZER_PIN 5

MFRC522 mfrc522(SS_PIN, RST_PIN);

const char* ssid = "Server 1";
const char* password = "Sebentar202";
const char* serverURL = "http://192.168.100.94/absensi/api/attendance.php";
const char* device_id = "ESP8266-1";

void setup() {
  Serial.begin(9600);
  SPI.begin();
  mfrc522.PCD_Init();

  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  WiFi.setAutoReconnect(true);
  WiFi.persistent(true);

  Serial.print("Menghubungkan ke WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nTerkoneksi ke WiFi!");
  Serial.print("IP ESP8266: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  if (!mfrc522.PICC_IsNewCardPresent()) return;
  if (!mfrc522.PICC_ReadCardSerial()) return;

  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    uid += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  Serial.print("UID Tag : ");
  Serial.println(uid);

  // Buzzer beep
  digitalWrite(BUZZER_PIN, HIGH);
  delay(100);
  digitalWrite(BUZZER_PIN, LOW);

  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    http.begin(client, serverURL);
    http.setTimeout(8000);
    http.addHeader("Content-Type", "application/json");

    String payload = "{\"device_id\":\"" + String(device_id) + "\",\"uid\":\"" + uid + "\"}";
    int httpCode = http.POST(payload);

    if (httpCode > 0) {
      Serial.printf("Data terkirim! (HTTP %d)\n", httpCode);
      Serial.println(http.getString());
    } else {
      Serial.printf("Gagal kirim: %s\n", http.errorToString(httpCode).c_str());
    }

    http.end();
  } else {
    Serial.println("WiFi terputus! Mencoba reconnect...");
    WiFi.reconnect();
  }

  delay(500);
  yield();
}
