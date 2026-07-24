import pyautogui as pg
import time
from solver import solve_captcha
import winsound
import keyboard
import sys
import random
import numpy as np

JUMLAH_DATA = 200
DELAY = 0.3
MAX_CAPTCHA_RETRY = 5
SLIDER_OFFSET = 40  # initial guess
SCALE_X = 1.10
LEARNING_RATE = 0.35
# =====================
# KOORDINAT
# =====================
BACK_CLICK_X = 2119 
BACK_CLICK_Y = 1580

BACK_X = 1722 #Koordinat untuk cek warna BACK (bukan klik)
BACK_Y = 1439

CEK_X = 2217
CEK_Y = 1557

NIK_BOX_X = 2129
NIK_BOX_Y = 917

SLIDER_X = 1894
SLIDER_Y = 1354

CAPTCHA_BOX_X = 1852 #Top-Left corner of CAPTCHA
CAPTCHA_BOX_Y = 807 #Top-Left corner of CAPTCHA
CAPTCHA_WIDTH = 616 #Width of CAPTCHA area (Bottom-Right_X - Top-Left_X)
CAPTCHA_HEIGHT = 456 #Height of CAPTCHA area (Bottom-Right_Y - Top-Left_Y)

POPUP_X = 2147
POPUP_Y = 1253
POPUP_COLOR = (98, 122, 46)

STOP_X = 1558 #Tambah Stop 
STOP_Y = 729
STOP_COLOR = (218, 95, 87)
# =====================
# STUCK PAGE DETECTION
# =====================
STUCK_X = 2685      
STUCK_Y = 430
STUCK_XX = 2515
STUCK_YY = 756      
STUCK_COLOR = (249, 239, 202) 

# Tombol LOGIN
LOGIN_BTN_X = 2040
LOGIN_BTN_Y = 1084
# =====================
# WARNA PATOKAN

# =====================
# WARNA PATOKAN
# =====================
CEK_COLOR = (98, 122, 46)
BACK_COLOR = (98, 122, 46)
LOGIN_COLOR = (98, 122, 46)
WebCopy = (73, 126, 211)

# =====================
# CAPTCHA STATUS CHECK (VERY IMPORTANT)
# =====================
# 👉 warna ini HARUS kamu ambil dari kondisi:
# - jika CAPTCHA BERHASIL (biasanya tombol berubah / hilang)
SUCCESS_X = 2111
SUCCESS_Y = 341
SUCCESS_COLOR = (166, 185, 87)  # ⚠️ ganti sesuai kondisi berhasil

# 👉 warna jika CAPTCHA masih muncul / gagal
FAIL_X = 2021
FAIL_Y = 948
FAIL_COLOR = (69, 70, 75)  # ⚠️ sesuaikan

# =====================
# HUMAN DRAG
# =====================
def wait_with_timeout(check_func, timeout=10, interval=0.3, label=""):
    start = time.time()

    while True:
        emergency_stop_check()

        if check_func():
            return True

        if time.time() - start > timeout:
            print(f"⏱ TIMEOUT: {label}")
            return False

        time.sleep(interval)
def emergency_stop_check():
    if keyboard.is_pressed('esc'):  # press ESC to stop
        print("🛑 EMERGENCY STOP PRESSED")
        sys.exit()
def stop_terdeteksi():
    return warna_mirip(pg.pixel(STOP_X, STOP_Y), STOP_COLOR, toleransi=15)
def captcha_berhasil():
    return warna_mirip(pg.pixel(SUCCESS_X, SUCCESS_Y), SUCCESS_COLOR)
def captcha_gagal():
    return warna_mirip(pg.pixel(FAIL_X, FAIL_Y), FAIL_COLOR)
def human_drag(start_x, start_y, distance):
    pg.moveTo(start_x, start_y, duration=0.15)
    pg.mouseDown()

    steps = 14
    current = 0

    for i in range(steps):
        progress = (i + 1) / steps
        ease = progress * progress + random.uniform(-0.02, 0.02)

        target = int(distance * ease)
        move = target - current
        current = target

        pg.moveRel(
            move,
            random.randint(-2, 2),
            duration=random.uniform(0.008, 0.02)
        )

    # 🔥 human-like correction
    pg.moveRel(2, 0, duration=0.04)
    pg.moveRel(-1, 0, duration=0.03)
    pg.moveRel(1, 0, duration=0.02)

    time.sleep(random.uniform(0.05, 0.12))
    pg.mouseUp()
def solve_captcha_with_retry():
    for attempt in range(1, MAX_CAPTCHA_RETRY + 1):
        emergency_stop_check()
        print(f"🧩 CAPTCHA attempt {attempt}")

        time.sleep(0.25)

        samples = []
        SAMPLE_COUNT = 4

        for _ in range(SAMPLE_COUNT):
            time.sleep(0.25)
            gx, score = solve_captcha(
                (CAPTCHA_BOX_X, CAPTCHA_BOX_Y, CAPTCHA_WIDTH, CAPTCHA_HEIGHT)
            )
            samples.append(gx)

        if not samples:
            continue

        gap_x = int(np.median(samples))
        gap_x = max(10, min(gap_x, CAPTCHA_WIDTH - 10))

        gap_screen_x = CAPTCHA_BOX_X + int(gap_x * SCALE_X)
        SLIDER_CENTER = 24

        distance = gap_screen_x - (SLIDER_X + SLIDER_CENTER)

        print("distance:", distance)

        human_drag(SLIDER_X, SLIDER_Y, distance)
        time.sleep(0.25)

        # optional tiny delay before correction scan
        time.sleep(0.95)

        # 🔥 recheck only if needed
        gx, score = solve_captcha(
            (CAPTCHA_BOX_X, CAPTCHA_BOX_Y, CAPTCHA_WIDTH, CAPTCHA_HEIGHT)
        )

        target = CAPTCHA_BOX_X + gx
        current = SLIDER_X + SLIDER_CENTER
        diff = target - current

        # 🚨 SECOND STOP CONDITION (IMPORTANT)
        if captcha_berhasil():
            print("✅ SUCCESS (post-check)")
            return True

        if abs(diff) > 80:
            print("⚠️ skip correction (too large)")
            continue

        if abs(diff) <= 10:
            print(f"🎯 micro fix {diff}px")

            pg.mouseDown()
            pg.moveRel(diff, 0, duration=0.06)
            pg.mouseUp()

            if captcha_berhasil():
                print("✅ SUCCESS after micro fix")
                return True

        print("❌ FAILED")

    return False
# =====================
# FUNGSI UTILITAS
# =====================
def warna_mirip(c1, c2, toleransi=10):
    return all(abs(a - b) <= toleransi for a, b in zip(c1, c2))


def cek_login():
    if warna_mirip(pg.pixel(LOGIN_BTN_X, LOGIN_BTN_Y), LOGIN_COLOR):
        print("⚠️ Logout terdeteksi → login ulang")
        pg.click(LOGIN_BTN_X, LOGIN_BTN_Y)
        time.sleep(3)
        return True
    return False


def tunggu_warna(x, y, warna, msg):
    print(f"Menunggu {msg} ...")
    while True:

        # jika logout saat menunggu
        if cek_login():
            return "LOGIN"

        current = pg.pixel(x, y)
        if warna_mirip(current, warna):
            return "OK"

        time.sleep(0.3)


def stuck_page_terdeteksi():
    current = pg.pixel(STUCK_XX, STUCK_YY)
    return warna_mirip(current, STUCK_COLOR)


def popup_muncul():
    current = pg.pixel(POPUP_X, POPUP_Y)
    return warna_mirip(current, POPUP_COLOR)


# =====================
# START BOT
# =====================
print("Bot mulai dalam 3 detik...")
time.sleep(3)

#Aku ubah loop utama menjadi while
i = 0
while not stop_terdeteksi():
    i += 1

    print(f"\nMemproses data ke-{i+1}")

    # =====================
    # 1️⃣ COPY DARI EXCEL (HANYA SEKALI)
    # =====================
    pg.hotkey('ctrl', 'b')
    time.sleep(DELAY)

    pg.hotkey('ctrl', 'c')
    time.sleep(DELAY)

    pg.press('down')
    time.sleep(DELAY)

    # =====================
    # 2️⃣ PROSES WEB (BOLEH DIULANG JIKA LOGOUT)
    # =====================
    ulang = True
    pertama_kali = True

    while ulang:
        emergency_stop_check()
        ulang = False

        # hanya pindah ke browser pertama kali saja
        if pertama_kali:
            pg.hotkey('alt', 'tab')
            time.sleep(0.5)
            pertama_kali = False

        # =====================
        # PASTE NIK
        # =====================
        pg.press('tab')
        time.sleep(0.4)
        pg.press('enter')
        time.sleep(0.3)

        pg.rightClick(NIK_BOX_X, NIK_BOX_Y)
        time.sleep(0.2)
        pg.press('p')
        time.sleep(0.3)

        pg.press('tab')
        pg.press('enter')

        if cek_login():
            ulang = True
            continue
        
        # =====================
        # POPUP (VERSI LEBIH STABIL)
        # =====================
        if stop_terdeteksi():
            print("🛑 STOP signal detected!")
            stop_all = True
            break
        if cek_login():
            ulang = True
            continue
        print("🔎 Mengecek popup...")

        popup_terdeteksi = False
        start_time = time.time()

        while time.time() - start_time < 0.5:  # cek selama 3 detik
            emergency_stop_check()
            if popup_muncul():
                popup_terdeteksi = True
                break
            time.sleep(0.2)

        if popup_terdeteksi:
            print("📢 Popup terdeteksi → klik tambahan")
            pg.click(POPUP_X, POPUP_Y)
            time.sleep(0.6)
        else:
            print("✅ Tidak ada popup")

        # =====================
        # STUCK PAGE (LEBIH STABIL)
        # =====================
        if stop_terdeteksi():
            print("🛑 STOP signal detected!")
            stop_all = True
            break
        if cek_login():
            ulang = True
            continue
        print("🔎 Mengecek stuck page...")

        stuck_terdeteksi = False
        start_time = time.time()

        while time.time() - start_time < 1.9:  # cek selama 4 detik
            if stuck_page_terdeteksi():
                stuck_terdeteksi = True
                break
            time.sleep(0.5)

        if stuck_terdeteksi:
            print("⚠️ Stuck page → klik & ulang browser")

            pg.click(STUCK_X, STUCK_Y)
            time.sleep(1)

            break
        else:
            print("✅ Tidak stuck")

        # =====================
        # TUNGGU CEK PESANAN
        # =====================
        if stop_terdeteksi():
            print("🛑 STOP signal detected!")
            stop_all = True
            break
        if cek_login():
            ulang = True
            continue

        ok = wait_with_timeout(
            lambda: warna_mirip(pg.pixel(CEK_X, CEK_Y), CEK_COLOR),
            timeout=10,
            label="CEK PESANAN"
        )

        if not ok:
            print("❌ Timeout CEK PESANAN → skip")
            continue

        pg.click(CEK_X, CEK_Y)
        time.sleep(0.5)

        pg.press('tab')
        pg.press('enter')

        # =====================
        # CAPTCHA AUTOMATIC SOLVER
        # =====================
        success = solve_captcha_with_retry()

        if not success:
            winsound.Beep(1500, 700)
            print("❌ CAPTCHA GAGAL TOTAL")
            continue
        # =====================
        # BACK
        # =====================
        if cek_login():
            ulang = True
            continue
        
        ok = wait_with_timeout(
            lambda: warna_mirip(pg.pixel(BACK_X, BACK_Y), BACK_COLOR),
            timeout=10,
            label="BACK BUTTON"
        )

        if not ok:
            print("❌ Timeout BACK → retry flow")
            continue
        
        pg.click(BACK_CLICK_X, BACK_CLICK_Y)
        time.sleep(0.5)

    # =====================
    # BALIK KE WEB
    # =====================
    pg.click(2055, 1579)
    time.sleep(0.3)
    
    pg.hotkey('alt', 'tab')
    time.sleep(0.5)
    
print("✅ Completed and Finish!")