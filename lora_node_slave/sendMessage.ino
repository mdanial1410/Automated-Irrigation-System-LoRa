void sendMessage(String outgoing) {
  LoRa.beginPacket();                // start packet
  LoRa.print(nodeID);
  LoRa.print(" ");
  LoRa.print(gatewayID);
  LoRa.print(" ");
  LoRa.print(outgoing);              // add payload/msg
  LoRa.endPacket();              // finish packet and send it
  Serial.println("Sending ->> " + nodeID + " " + gatewayID + " " + outgoing);
}
/*
   Message format:
   Node Gateway Message
   0x01 0x99 hello world
*/

void broadcastMessage(String outgoing) {
  LoRa.beginPacket();                // start packet
  LoRa.print(nodeID);
  LoRa.print(" ");
  LoRa.print(outgoing);              // add payload/msg
  LoRa.endPacket();              // finish packet and send it
  Serial.println("Sending ->> " + nodeID + " " + outgoing);
}
