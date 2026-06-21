# MicroPython MFRC522 RFID library stub/implementation helper
# Library can be replaced with full community version for physical hardware.

class MFRC522:
    REQIDL = 0x26
    REQALL = 0x52
    AUTHENT1A = 0x60
    AUTHENT1B = 0x61
    OK = 0
    NOTAGERR = 1
    ERR = 2

    def __init__(self, spi, gpioRst, gpioCs):
        self.spi = spi
        self.rst = gpioRst
        self.cs = gpioCs

    def request(self, req_mode):
        # Scan for cards, returns status and tag type
        # For physical devices, replace with standard community mfrc522.py
        return (self.OK, 0x04)

    def anticoll(self):
        # Returns status and raw UID bytes
        return (self.OK, [0xA1, 0xB2, 0xC3, 0xD4])
