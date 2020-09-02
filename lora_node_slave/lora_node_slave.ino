// This sketch is for a LoRa node acting as a slave
// This Node has Soil Sensor and LED ONLY
// Send sensor value to Gateway.
// Dynamic Tx and SF values
// turn-based collision avoidance mechanism
/*
    LoRa Pin
    2 (DIO0),
    9 (NRESET),
    10 (NSS),
    11 (MOSI),
    12 (MISO),
    13 (SCK)
    863-870MHz or 902-928MHz

    LED PWM Pins
    3,5,6

    Soil Sensor Analog Pins
    A3
*/

#include <LoRa.h>
#include "global.h"

//>>>>>>>>>>>>> REMEMBER TO CHANGE SOURCE AND DESTINATION ID <<<<<<<<<<<<<<<<<<<<<<
const String nodeID = "0x02";            // Source ID
const String gatewayID = "0x99";         // Gateway ID
const String prev_nodeID = "0x01";

// For Soil sensor
//>>>>>>>>>>>>> CHANGE THIS ACCORDINGLY <<<<<<<<<<<<<<<<<<<<<<
String sensorName = "Sensor 2";
const int soilPin = A3;                  // Analog pin

bool ack = false, ack_prev = false;   // acknowledgement flag
int retransmit_count = 0;
long re_txprevMillis = 0;
long sleepPreviousMillis = 0;
long sleepInterval = 10000;

void setup() {
  Serial.begin(115200);
  while (!Serial);

  Serial.println("LoRa Node to Gateway 4. Node: " + nodeID);

  if (!LoRa.begin(CH)) {
    Serial.println("LoRa init failed. Check your connections.");
    while (true);                           // if fail, do nothing
  }

  LoRa.setFrequency(CH);
  LoRa.setSpreadingFactor(SF);
  LoRa.setTxPower(current_tx_power);
  LoRa.setSignalBandwidth(bandwidth);
  LoRa.setCodingRate4(CR);
  LoRa.setPreambleLength(10);
  LoRa.setSyncWord(syncWord);
  LoRa.enableCrc();

  // LED RGB PWM pins
  pinMode(red, OUTPUT);
  pinMode(green, OUTPUT);
  pinMode(blue, OUTPUT);

  rgb(80, 0, 0, 200);
  rgb(0, 80, 0, 200);
  rgb(0, 0, 80, 200);
  rgb(0, 0, 0, 0);

  Serial.println("LoRa init succeeded. Node ID: " + nodeID);
  Serial.print("Freq: " + String((CH / 1E6)));
  Serial.print(" SF: " + String(SF));
  Serial.println();

  LoRa.onReceive(onReceive);
  LoRa.receive();
}

void loop() {
  ack = false;
  if (new_tx_power != current_tx_power) {
    //    changePowerAndSF();       // uncomment this to enable dynamic power and SF
    changeTxPower();
    current_tx_power = new_tx_power;
    Serial.println("Current Tx power: " + String(current_tx_power));
    LoRa.setTxPower(current_tx_power);
  }

  while (ack_prev) {
    // when prev node has send ack, send current node payload
    re_txprevMillis = millis();
    sensorReading = analogRead(soilPin);
    if (sensorReading > 455) {
      // >= 455 = dry
      rgb(50, 0, 0, 0);
    } else {
      rgb(0, 0, 0, 0);
    }
    msg = sensorName + " " + String(sensorReading);
    sendMessage(msg);
    LoRa.receive();

    while (!ack) {
      // as long as no ack for current node, re-transmit every 1 sec for 3 times
      if (retransmit_count > 2) {
        // after 3 unsuccessful retries, reset the flags
        ack_prev = false;
        ack = true;               // exit condition
        retransmit_count = 0;     // reset count
        msg = "nothing heard";
        sendMessage(msg);
        LoRa.receive();
        break;                    // exit while loop
      }
      if (millis() - re_txprevMillis > 1000) {
        // re-transmit every 1 sec
        re_txprevMillis = millis();
        sendMessage(msg);
        retransmit_count++;
        Serial.println("Retry :" + String(retransmit_count));
        LoRa.receive();
      }
    }
    if (ack) {
      msg = "out";
      broadcastMessage(msg);
      Serial.println();
      ack = false;
      ack_prev = false;
      LoRa.receive();
    }
  }
}
