void rgb(int r, int g, int b, int pause) {
  analogWrite(red, r);
  analogWrite(green, g);
  analogWrite(blue, b);
  delay(pause);
}
