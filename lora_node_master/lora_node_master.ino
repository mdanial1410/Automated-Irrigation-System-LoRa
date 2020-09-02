// This sketch is for a LoRa node acting as a slave
// This Node has Soil Sensor and LED ONLY
// Send sensor value to Gateway.
// Dynamic Tx and SF values
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
const String nodeID = "0x01";            // Source ID
const String gatewayID = "0x99";         // Gateway ID

// For Soil sensor
//>>>>>>>>>>>>> CHANGE THIS ACCORDINGLY <<<<<<<<<<<<<<<<<<<<<<
String sensorName = "Sensor 1";
const int soilPin = A3;                  // Analog pin

bool ack = false;   // acknowledgement flag
int retransmit_count = 0;
long re_txprevMillis = 0;

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

  Serial.println("LoRa init succeeded. Node ID: " + nodeID);
  Serial.print("Freq: " + String((CH / 1E6)));
  Serial.print(" SF: " + String(SF));
  Serial.println();

  LoRa.onReceive(onReceive);
  LoRa.receive();
}

void loop() {
  if (new_tx_power != current_tx_power) {
    //    changePowerAndSF();
    changeTxPower();
    current_tx_power = new_tx_power;
    Serial.println("Current Tx power: " + String(current_tx_power));
    LoRa.setTxPower(current_tx_power);
  }

  if (millis() - txpreviousMillis > txInterval) {
    // occurs every 5 sec
    txpreviousMillis = millis();
    //    re_txprevMillis = 0;    //<<<<<<<<<< might not be necessary
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
    re_txprevMillis = txpreviousMillis;

    while (!ack) {
      // as long as no ack, re-transmit every 1 sec for 3 times
      if (retransmit_count > 2) {
        ack = true;               // exit condition
        retransmit_count = 0;     // reset count
        msg = "nothing heard";
        sendMessage(msg);
        LoRa.receive();
        break;
      }

      if (millis() - re_txprevMillis > 1000) {
        re_txprevMillis = millis();
        sendMessage(msg);
        retransmit_count++;
        Serial.println("Retry :" + String(retransmit_count));
        LoRa.receive();
      }
    }
  }
  if (ack) {
    msg = "out";
    broadcastMessage(msg);
    ack = false;
    LoRa.receive();
  }
}
