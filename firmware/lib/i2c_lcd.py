# MicroPython I2C LCD driver stub/helper
from lcd_api import LcdApi

class I2cLcd(LcdApi):
    def __init__(self, i2c, i2c_addr, num_lines, num_columns):
        super().__init__(num_lines, num_columns)
        self.i2c = i2c
        self.i2c_addr = i2c_addr
