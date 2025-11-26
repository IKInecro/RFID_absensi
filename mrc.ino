#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 15
#define RST_PIN 0
#define BUZZER_PIN 5
#define LED_DISCONNECTED 4  // D2 (GPIO4) - LED merah untuk WiFi terputus
#define LED_CONNECTED 2     // D4 (GPIO2) - LED hijau untuk WiFi terhubung

MFRC522 mfrc522(SS_PIN, RST_PIN);

// WiFi credentials - Primary
const char* ssid1 = "Server 1";
const char* password1 = "Sebentar202#";

// WiFi credentials - Backup
const char* ssid2 = "Server 5";  // Ganti dengan SSID backup lu
const char* password2 = "Seberntar202";  // Ganti dengan password backup lu

// GANTI IP INI SESUAI DENGAN IP KOMPUTER KAMU (Cek pakai cmd > ipconfig)
const char* serverURL = "http://192.168.100.184/RFID/api/attendance.php";
const char* device_id = "ESP8266-1";

void updateLEDStatus(bool connected) {
  if (connected) {
    digitalWrite(LED_DISCONNECTED, LOW);  // Matikan LED merah
    digitalWrite(LED_CONNECTED, HIGH);    // Nyalakan LED hijau
  } else {
    digitalWrite(LED_DISCONNECTED, HIGH); // Nyalakan LED merah
    digitalWrite(LED_CONNECTED, LOW);     // Matikan LED hijau
  }
}

bool connectToWiFi() {
  Serial.println("\n=== Mencoba koneksi WiFi ===");
  updateLEDStatus(false);  // Set LED ke status disconnected
  
  // Coba SSID 1 dulu
  Serial.print("Mencoba: ");
  Serial.println(ssid1);
  WiFi.begin(ssid1, password1);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nTerkoneksi ke: " + String(ssid1));
    Serial.print("IP ESP8266: ");
    Serial.println(WiFi.localIP());
    updateLEDStatus(true);  // Set LED ke status connected
    return true;
  }
  
  // Kalau gagal, coba SSID 2
  Serial.println("\nGagal! Mencoba SSID backup...");
  Serial.print("Mencoba: ");
  Serial.println(ssid2);
  
  WiFi.disconnect();
  delay(1000);
  WiFi.begin(ssid2, password2);
  
  attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nTerkoneksi ke: " + String(ssid2));
    Serial.print("IP ESP8266: ");
    Serial.println(WiFi.localIP());
    updateLEDStatus(true);  // Set LED ke status connected
    return true;
  }
  
  Serial.println("\nGagal koneksi ke semua WiFi!");
  updateLEDStatus(false);  // Set LED ke status disconnected
  return false;
}

void setup() {
  Serial.begin(9600);
  SPI.begin();
  mfrc522.PCD_Init();

  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  // Setup LED pins
  pinMode(LED_DISCONNECTED, OUTPUT);
  pinMode(LED_CONNECTED, OUTPUT);
  
  // Initial state: disconnected
  updateLEDStatus(false);

  WiFi.mode(WIFI_STA);
  WiFi.setAutoReconnect(true);
  WiFi.persistent(true);

  connectToWiFi();
}

void loop() {
  // Cek koneksi WiFi, kalau putus coba reconnect
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi terputus! Mencoba reconnect...");
    updateLEDStatus(false);  // Update LED ke status disconnected
    connectToWiFi();
  }

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
    Serial.println("WiFi terputus! Gagal kirim data.");
    updateLEDStatus(false);  // Pastikan LED menunjukkan status disconnected
  }

  delay(500);
  yield();
}