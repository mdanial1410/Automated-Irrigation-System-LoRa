void onReceive(int packetSize) {
  if (packetSize == 0) return;          // if there's no packet, return

  String incoming = "";                 // payload of packet

  // read packet
  while (LoRa.available()) {            // can't use readString() in callback, so
    incoming += (char)LoRa.read();      // add bytes one by one
  }

  /*
     Format of incoming packet:
     Gateway Node Message
     0x99 0x01 increase power
     012345678901234567890123
     0x99 0x01 ack
  */

  if (incoming.substring(0, 4) != gatewayID || incoming.substring(5, 9) != nodeID) {
    // for debugging
//    Serial.println("Message: " + String(incoming));/
    return;
  }

  if (incoming.substring(10, 24) == "increase power") {
    if (!upper_limit) {
      new_tx_power += 1;
    }
  } else if (incoming.substring(10, 24) == "decrease power") {
    if (!lower_limit) {
      new_tx_power -= 1;
    }
  } else if (incoming.substring(5, 13) == nodeID +" ack") {
    // ack packet from gateway
    ack = true;
  }

  Serial.println("Received Message <<- " + incoming);
  Serial.println();
} // End of onReceive()
