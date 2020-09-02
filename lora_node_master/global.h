long int  channels [9] = { (long)867.3E6, (long)867.5E6, (long)867.7E6,
                           (long)867.9E6, (long)868.1E6, (long)868.3E6,
                           (long)868.5E6, (long)868.7E6, (long)868.9E6
                         };

int SF = 7;                             //Spreading factor 7-12
int current_tx_power = 5;               //Tx Power 2-20
int new_tx_power = current_tx_power;

const long CH = channels [0];           //Tx Channel
const int CR = 8;
const byte syncWord = 0x55;             // Similar to PSK
const long bandwidth = 125000;

// Delay (in ms)
unsigned long currentMillis = 0;
long txpreviousMillis = 0;
const int txInterval = 3000;

// Flags for changing power & SF
boolean upper_limit = false, lower_limit = false;

String msg;
int sensorReading = 0;
int timeToSend = 0;

// LED PWM Pins
const int red = 6;
const int green = 5;
const int blue = 3;
