void changePowerAndSF() {
  if (new_tx_power > 20) {
    if (SF < 11) {
      if (SF == 7) {
        lower_limit = false;
      }
      SF++;
      new_tx_power = 2;
      Serial.println("SF increased: " + String(SF));
      if ( SF > 10) {
        upper_limit = true;
        SF = 10;
        new_tx_power = 20; // Max tx power
        Serial.println("SF upper LIMIT!!: " + String(SF));
        msg = "upper";
        sendMessage(msg);
        Serial.println("Sending ->> " + msg);
        delay(500);
      }
      // if SF changes, inform gateway before changing
      msg = "new SF: " + String(SF);
      sendMessage(msg);
      Serial.println("Sending ->> " + msg);
      LoRa.setSpreadingFactor(SF);
    }
  } else if (new_tx_power < 2) {
    if (SF > 6) {
      if (SF == 10) {
        upper_limit = false;
      }
      SF--;
      new_tx_power = 20;
      Serial.println("SF decreased: " + String(SF));
      if (SF < 7) {
        lower_limit = true;
        SF = 7;
        new_tx_power = 2; // lowest power
        Serial.println("SF lower LIMIT!!: " + String(SF));
        msg = "lower";
        sendMessage(msg);
        Serial.println("Sending ->> " + msg);
      }
      // if SF changes, inform gateway before changing
      msg = "new SF: " + String(SF);
      sendMessage(msg);
      Serial.println("Sending ->> " + msg);
      LoRa.setSpreadingFactor(SF);
    }
  }
}

void changeTxPower() {
  if (new_tx_power > 20) {
    new_tx_power = 20;
    Serial.println("Max Tx power!: " + String(new_tx_power));
    msg = "upper";
    sendMessage(msg);
  } else if (new_tx_power < 2) {
    new_tx_power = 2;
    Serial.println("Min Tx power!: " + String(new_tx_power));
    msg = "lower";
    sendMessage(msg);
  }
  delay(500);
}
