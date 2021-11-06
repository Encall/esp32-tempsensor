/*
  By Encall
  Work with ESP32
    * DHT22
    * LCD_I2C 16x2
    * LineNotify
*/
#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include "DHT.h"
#include <LiquidCrystal_I2C.h>
#include <TridentTD_LineNotify.h>

LiquidCrystal_I2C lcd(0x27, 16, 2);   //Module IIC/I2C Interface
#define DHTPIN 14     //Pin of DHT22
#define DHTTYPE DHT22   // DHT 22  (AM2302), AM2321
DHT dht(DHTPIN, DHTTYPE);
#define LINE_TOKEN  "GKBu6JVAtIwJ4YlaJqB4L5bAVIjwVkjRjpRebDq0ZLr"   //Token LineNotify

const char* ssid = "NKP Home";
const char* password = "88888888";

// Domain name with URL path or IP address with path
const char* serverName = "http://live.nkpcoldstorage.com/esp-post-data.php";

// API key for checking with POST
String apiKeyValue = "tPmAT5Ab3j7F9";
String sensorName = "DHT22";
String sensorLocation = "Freezer3";
String versionINO = "1.3";

// POST Data to DB Timer, 30s(30000)
unsigned long lastTime = 0;
unsigned long timerDelay = 30000;
// Line Notify Timer 1 HR (3600000) but for testing 30s(30000)
const unsigned long LineTime = 3600000;
unsigned long lastLineTime = 0;

void setup() {
  lcd.begin();
  lcd.backlight();
  lcd.home();
  lcd.clear();
  Serial.begin(115200);
  WiFi.disconnect(true);
  delay(1000);
  WiFi.onEvent(WiFiStationConnected, SYSTEM_EVENT_STA_CONNECTED);
  WiFi.onEvent(WiFiGotIP, SYSTEM_EVENT_STA_GOT_IP);
  WiFi.onEvent(WiFiStationDisconnected, SYSTEM_EVENT_STA_DISCONNECTED);
  WiFi.begin(ssid, password);
  delay(3500);
  dht.begin();
  Serial.println(LINE.getVersion());
  lcd.clear();
  lcd.home();
  lcd.print("WIFI Temp Probe");
  lcd.setCursor(0, 1);
  lcd.print("Version: ");
  lcd.print(versionINO);
  delay(3500);
  lcd.clear();
  lcd.home();
  lcd.print("Starting....");
  lcd.setCursor(0, 1);
  lcd.print("Node: ");
  lcd.print(sensorLocation);
  LINE.setToken(LINE_TOKEN);
}

void loop() {
  sentData();
  lineNotify();
}

void sentData() {
    //Send an HTTP POST request every 10 minutes
  if ((millis() - lastTime) > timerDelay) {
    //Check WiFi connection status
    if(WiFi.status()== WL_CONNECTED){
      WiFiClient client;
      HTTPClient http;

      // Domain name with URL path or IP address with path
      http.begin(client, serverName);

      // Specify content-type header
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");

      float h = dht.readHumidity();
      float t = dht.readTemperature();
      float hic = dht.computeHeatIndex(t, h, false);
      
      // Prepare your HTTP POST request data
      String httpRequestData = "api_key=" + apiKeyValue + "&sensor=" + sensorName
                            + "&location=" + sensorLocation + "&value1=" + t
                            + "&value2=" + h + "&value3=" + hic + "";
      lcd.clear();
      lcd.home();
      lcd.print("Temp: ");
      lcd.print(t);
      lcd.print(" C");
      lcd.setCursor(0, 1);
      lcd.print("Humidity: ");
      lcd.print(h);
      lcd.print("%");
      
      Serial.print("httpRequestData: ");
      Serial.println(httpRequestData);
      
      // Send HTTP POST request
      int httpResponseCode = http.POST(httpRequestData);
      if (httpResponseCode>0) {
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
      }
      else {
        Serial.print("Error code: ");
        Serial.println(httpResponseCode);
      }
      // Free resources
      http.end();
    }
    else {
      Serial.println("WiFi Disconnected");
    }
    lastTime = millis();
  }
}

void lineNotify() {
  unsigned long currentTime = millis();

  if( currentTime - lastLineTime >= LineTime){
      float h = dht.readHumidity();
      float t = dht.readTemperature();
      String LineText;
      String TXT1 = " ขณะนี้อุณหภูมิ: ";
      String TXT2 = "°C ";
      String TXT3 = "ความชื้น: ";
      String TXT4 = "%";
      LineText = sensorLocation + TXT1 + t + TXT2 + TXT3 + h + TXT4;
      Serial.print("Line ");
      Serial.println(LineText);
      LINE.notify(LineText);
      lastLineTime = currentTime;
  }
}


void WiFiStationConnected(WiFiEvent_t event, WiFiEventInfo_t info){
  Serial.println("Connected to AP");
  lcd.clear();
  lcd.home();
  lcd.print("Connected to AP");
  delay(3500);
}

void WiFiGotIP(WiFiEvent_t event, WiFiEventInfo_t info){
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
  lcd.clear();
  lcd.home();
  lcd.print("WiFi connected");
  lcd.setCursor(0, 1);
  lcd.print(WiFi.localIP());
  delay(3500);
}

void WiFiStationDisconnected(WiFiEvent_t event, WiFiEventInfo_t info){
  Serial.println("Disconnected from WiFi access point");
  lcd.clear();
  lcd.home();
  lcd.print("WiFi Disconnected");
  Serial.print("WiFi lost connection. Reason: ");
  lcd.setCursor(0, 1);
  lcd.print(info.disconnected.reason);
  delay(3500);
  Serial.println(info.disconnected.reason);
  Serial.println("Trying to Reconnect");
  lcd.clear();
  lcd.home();
  lcd.print("Reconnecting...");
  WiFi.begin(ssid, password);
  delay(3500);
}
