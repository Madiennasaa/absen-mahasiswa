import network
import urequests
import time
import ujson
from machine import Pin, SPI, I2C, PWM
from mfrc522 import MFRC522
from i2c_lcd import I2cLcd
import secrets

# Pin definitions
LED_PIN = 25
BUZZER_PIN = 27
LCD_SDA = 21
LCD_SCL = 22

# Setup LED and Buzzer
led = Pin(LED_PIN, Pin.OUT)
led.value(0)

# Setup I2C LCD
i2c = I2C(0, sda=Pin(LCD_SDA), scl=Pin(LCD_SCL), freq=400000)
# Scan for I2C devices to find address
devices = i2c.scan()
if devices:
    lcd = I2cLcd(i2c, devices[0], 2, 16)
else:
    print("LCD I2C not found!")
    lcd = None

def lcd_print(line1, line2=""):
    if lcd:
        lcd.clear()
        lcd.putstr(line1[:16] + "\n" + line2[:16])

def play_beep(freq, duration_ms):
    # Buzzer pasif needs PWM
    buzzer = PWM(Pin(BUZZER_PIN))
    buzzer.freq(freq)
    buzzer.duty(512) # ~50% duty cycle
    time.sleep_ms(duration_ms)
    buzzer.duty(0)
    buzzer.deinit()

def connect_wifi():
    lcd_print("Connecting WiFi", secrets.WIFI_SSID)
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    if not wlan.isconnected():
        wlan.connect(secrets.WIFI_SSID, secrets.WIFI_PASSWORD)
        for _ in range(20):
            if wlan.isconnected():
                break
            time.sleep(0.5)
    if wlan.isconnected():
        print("Connected to WiFi:", wlan.ifconfig()[0])
        lcd_print("WiFi Connected!", wlan.ifconfig()[0])
        time.sleep(1.5)
        return True
    else:
        print("WiFi Connection Failed!")
        lcd_print("WiFi Gagal!", "Cek secrets.py")
        time.sleep(2)
        return False

# Setup SPI for RC522
spi = SPI(2, baudrate=2500000, polarity=0, phase=0, sck=Pin(18), mosi=Pin(23), miso=Pin(19))
rdr = MFRC522(spi, gpioRst=4, gpioCs=5)

lcd_print("Absensi RFID", "Siap Scan Kartu")

# Connect to WiFi
wifi_ok = connect_wifi()
if not wifi_ok:
    lcd_print("Mode Offline", "Sambungkan WiFi")

while True:
    # Scan for cards
    (stat, tag_type) = rdr.request(rdr.REQIDL)
    if stat == rdr.OK:
        (stat, raw_uid) = rdr.anticoll()
        if stat == rdr.OK:
            # Format UID to hex string
            uid_str = "".join("{:02X}".format(x) for x in raw_uid)
            print("Detected UID:", uid_str)
            
            # 1. LED hijau menyala first
            led.value(1)
            
            # 2. Buzzer pasif berbunyi sesaat SETELAH LED menyala
            time.sleep_ms(50)
            play_beep(1000, 150)
            
            # 3. LCD tampilkan "Memproses..."
            lcd_print("Memproses...", "UID: " + uid_str)
            
            # Check wifi connection
            wlan = network.WLAN(network.STA_IF)
            if not wlan.isconnected():
                lcd_print("Koneksi Gagal", "Coba Lagi")
                led.value(0)
                time.sleep(2)
                lcd_print("Absensi RFID", "Siap Scan Kartu")
                continue
                
            # Send HTTP POST
            try:
                headers = {
                    'Content-Type': 'application/json',
                    'X-Device-Key': secrets.DEVICE_KEY
                }
                payload = ujson.dumps({"uid": uid_str})
                
                print("Sending POST request to:", secrets.API_URL)
                response = urequests.post(secrets.API_URL, data=payload, headers=headers)
                
                # Turn off LED green once response is received
                led.value(0)
                
                resp_json = response.json()
                response.close()
                
                status_code = resp_json.get("code", response.status_code)
                print("Response Code:", status_code)
                print("Response Content:", resp_json)

                if status_code == 200:
                    data = resp_json.get("data", {})
                    nama = data.get("nama", "Mahasiswa")
                    status_kehadiran = data.get("status", "Hadir")
                    lcd_print(nama, status_kehadiran)
                    # Play happy tone
                    play_beep(2000, 100)
                    time.sleep_ms(100)
                    play_beep(2500, 100)
                elif status_code == 404:
                    lcd_print("Kartu Tidak", "Terdaftar")
                    play_beep(500, 400)
                elif status_code == 400:
                    lcd_print("Sudah Absen", "Hari Ini")
                    play_beep(800, 300)
                else:
                    lcd_print("Error: " + str(status_code), resp_json.get("message", "Gagal"))
                    play_beep(500, 500)
                    
            except Exception as e:
                print("HTTP Error:", e)
                led.value(0)
                lcd_print("Koneksi Gagal", "Coba Lagi")
                play_beep(400, 600)
            
            # Wait a few seconds before returning to standby screen
            time.sleep(3)
            lcd_print("Absensi RFID", "Siap Scan Kartu")
            
    time.sleep_ms(100)
