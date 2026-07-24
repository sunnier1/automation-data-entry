from turtle import delay

import pyautogui as pg
import time
from solver import solve_captcha
import winsound
import random
import numpy as np
import keyboard
import sys

DELAY = 0.3
MAX_CAPTCHA_RETRY = 5
SLIDER_OFFSET = 40  # initial guess
SCALE_X = 1.09
LEARNING_RATE = 0.35
# =====================
# KOORDINAT
# =====================
BACK_CLICK_X = 1872
BACK_CLICK_Y = 1571

BACK_X = 1722
BACK_Y = 1439

CLEAR_COPY_X = 1226 #1248
CLEAR_COPY_Y = 352 #446

TUTUP_X = 2128
TUTUP_Y = 1128

CEK_X = 2217
CEK_Y = 1557

NIK_BOX_X = 1988 #2129
NIK_BOX_Y = 940 #917

SLIDER_X = 1894
SLIDER_Y = 1354

CAPTCHA_BOX_X = 1852 #Top-Left corner of CAPTCHA
CAPTCHA_BOX_Y = 807 #Top-Left corner of CAPTCHA
CAPTCHA_WIDTH = 616 #Width of CAPTCHA area (Bottom-Right_X - Top-Left_X)
CAPTCHA_HEIGHT = 456 #Height of CAPTCHA area (Bottom-Right_Y - Top-Left_Y)

POPUP_X = 2147 #BUTTON HIJAU
POPUP_Y = 1253 #BUTTON HIJAU
POPUP_COLOR = (98, 122, 46) # warna patokan pada BUTTON HIJAU untuk deteksi popup (sesuaikan dengan koordinat)

POPUP_STUCK_BLUE_X = 1831
POPUP_STUCK_BLUE_Y = 1345
POPUP_STUCK_BLUE_COLOR = (229, 242, 253)

STOP_X = 1544 #Tambah Stop 
STOP_Y = 729
STOP_COLOR = (218, 95, 87)

WEB_ERROR_X = 2578
WEB_ERROR_Y = 621
WEB_ERROR_COLOR = (243, 213, 103)

WEB_ERROR_CLICK_X = 1762
WEB_ERROR_CLICK_Y = 1066
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

#======================
# Data meninggal
#======================
NIK_meninggal_X = 1800
NIK_meninggal_Y = 1085
nik_meninggal_color =  (228, 137, 74)
# Klik Tutup Jika Data Meninggal
TUTUP_MENINGGAL_X = 1594 #klik dimana saja 
TUTUP_MENINGGAL_Y = 286

scroll_down_X = 93
scroll_down_Y = 1613

hapus_data_X = 388
hapus_data_Y = 1503
hapus_data_color = (201, 197, 213)

confirm_hapus_X =923 #912
confirm_hapus_Y = 730 #811
confirm_hapus_color = (186, 65, 55)

scroll_up_X = 1339
scroll_up_Y = 1614
# =====================
# Web copy
# =====================
WebX = 983 #990
WebY = 678 #745

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

        check_stop_and_exit()

        # SUCCESS
        if check_func():
            return "SUCCESS"

        # HANDLE POPUP
        if popup_muncul():
            handle_popup()

        # HANDLE STUCK
        if stuck_page_terdeteksi():

            handle_stuck_page()

            return "STUCK"
        #HANDLE DATA MENINGGAL
        if cek_data_meninggal():
            hapus_data_meninggal()
            return "DATA MENINGGAL"

        # HANDLE TIMEOUT
        if time.time() - start > timeout:

            print(f"⏱ TIMEOUT: {label}")

            # cek popup sebelum gagal
            if popup_muncul():
                handle_popup()
                start = time.time()
                continue

            # cek stuck sebelum gagal
            if stuck_page_terdeteksi():
                handle_stuck_page()
                return "STUCK"
            
            if cek_data_meninggal():
                hapus_data_meninggal()
                return "DATA MENINGGAL"

            return "TIMEOUT"

        time.sleep(interval)
def handle_popup():

    popup_terdeteksi = False
    start_time = time.time()

    while time.time() - start_time < 1.3:

        check_stop_and_exit()

        if popup_muncul():
            popup_terdeteksi = True
            break

        time.sleep(0.2)

    if popup_terdeteksi:

        print("📢 Popup terdeteksi → klik tambahan")

        pg.click(2159, 976)
        time.sleep(0.6)
        if not popup_stuck():
            print("✅ Popup Terlewati")        
            pg.click(POPUP_X, POPUP_Y)
            time.sleep(0.6)
        pg.click(1949, 1592)
        return True

    return False
def error_web_terdeteksi():
    return warna_mirip(pg.pixel(WEB_ERROR_X, WEB_ERROR_Y), WEB_ERROR_COLOR, toleransi=15)
def stop_terdeteksi():
    return warna_mirip(pg.pixel(STOP_X, STOP_Y), STOP_COLOR, toleransi=15)
def check_stop_and_exit():
    if stop_terdeteksi():
        winsound.Beep(1500, 700)
        print("🛑 STOP DETECTED → EXIT")
        sys.exit()
def captcha_berhasil():
    return warna_mirip(pg.pixel(SUCCESS_X, SUCCESS_Y), SUCCESS_COLOR)
def captcha_gagal():
    return warna_mirip(pg.pixel(FAIL_X, FAIL_Y), FAIL_COLOR)
def human_drag(start_x, start_y, distance):
    pg.moveTo(start_x, start_y, duration=0.009)
    pg.mouseDown()

    steps = 10
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
            duration=random.uniform(0.005, 0.015)
        )

    # 🔥 human-like correction
    pg.moveRel(2, 0, duration=0.04)
    pg.moveRel(-1, 0, duration=0.03)
    pg.moveRel(1, 0, duration=0.02)

    time.sleep(random.uniform(0.05, 0.12))
    pg.mouseUp()
def solve_captcha_with_retry():
    for attempt in range(1, MAX_CAPTCHA_RETRY + 1):
        print(f"🧩 CAPTCHA attempt {attempt}")

        samples = []
        SAMPLE_COUNT = 4

        for _ in range(SAMPLE_COUNT):
            time.sleep(0.05)
            if captcha_berhasil():
                print("✅ SUCCESS")
                return True
            gx, score = solve_captcha(
                (CAPTCHA_BOX_X, CAPTCHA_BOX_Y, CAPTCHA_WIDTH, CAPTCHA_HEIGHT)
            )
            samples.append(gx)

        if not samples:
            continue
        
        if captcha_berhasil():
            print("✅ SUCCESS (Pre-solve check)")
            return True
        gap_x = int(np.median(samples))
        gap_x = max(10, min(gap_x, CAPTCHA_WIDTH - 10))

        gap_screen_x = CAPTCHA_BOX_X + int(gap_x * SCALE_X)
        SLIDER_CENTER = 24

        distance = gap_screen_x - (SLIDER_X + SLIDER_CENTER)
        if captcha_berhasil():
            print("✅ SUCCESS")
            return True
        print("distance:", distance)

        human_drag(SLIDER_X, SLIDER_Y, distance)
        time.sleep(0.25)

        # optional tiny delay before correction scan
        time.sleep(0.19)

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

def error_web():
    if error_web_terdeteksi():
        print("⚠️‼️ Web error terdeteksi → klik & ulang")
        pg.click(WEB_ERROR_CLICK_X, WEB_ERROR_CLICK_Y)
        time.sleep(1)
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

def cek_data_meninggal():
    current = pg.pixel(NIK_meninggal_X, NIK_meninggal_Y)
    return warna_mirip(current, nik_meninggal_color)
def hapus_data_meninggal():
    if not cek_data_meninggal():
        return False
    print("⚠️ Data meninggal terdeteksi → hapus")
    pg.click(TUTUP_MENINGGAL_X, TUTUP_MENINGGAL_Y)
    time.sleep(0.5)
    pg.click(scroll_down_X, scroll_down_Y)
    time.sleep(2)
    pg.click(hapus_data_X, hapus_data_Y)
    time.sleep(1)
    pg.click(confirm_hapus_X, confirm_hapus_Y)
    time.sleep(0.9)
    pg.click(scroll_up_X, scroll_up_Y)
    time.sleep(1.2)
    return True
def popup_muncul():
    current = pg.pixel(POPUP_X, POPUP_Y)
    return warna_mirip(current, POPUP_COLOR)
def popup_stuck():
    current = pg.pixel(POPUP_STUCK_BLUE_X, POPUP_STUCK_BLUE_Y)
    return warna_mirip(current, POPUP_STUCK_BLUE_COLOR)
def cek_pesanan():
    current = pg.pixel(CEK_X, CEK_Y)
    return warna_mirip(current, CEK_COLOR)
def handle_popup_stuck():
    if not popup_stuck():
        return False

    print("⚠️ Popup stuck → Skip Data")

    pg.click(1583, 494)
    time.sleep(0.6)
    return True
def handle_stuck_page():
    if not stuck_page_terdeteksi():
        return False

    print("⚠️ Stuck page → Lewati data")

    pg.click(STUCK_X, STUCK_Y)
    time.sleep(1)

    pg.click(TUTUP_X, TUTUP_Y)
    time.sleep(0.3)
    print("⌛ waiting recovery...")
    time.sleep(0.5)

    return True

# =====================
# START BOT
# =====================
print("Bot mulai dalam 3 detik...")
time.sleep(3)

#Aku ubah loop utama menjadi while
i = 1
while True:
    check_stop_and_exit()

    print(f"\nMemproses data ke-{i}")
    i += 1

    # =====================
    # 1️⃣ COPY DARI WEB (HANYA SEKALI)
    # =====================
    pg.click(CLEAR_COPY_X, CLEAR_COPY_Y) #clear copy
    time.sleep(0.3)
    
    pg.click(WebX, WebY)  
    time.sleep(0.2)

    # =====================
    # 2️⃣ PROSES WEB (BOLEH DIULANG JIKA LOGOUT)
    # =====================
    ulang = True
    pertama_kali = True

    while ulang:
        check_stop_and_exit()
        ulang = False

        # hanya pindah ke browser pertama kali saja
        if pertama_kali:
        #     pg.hotkey('alt', 'tab')
        #     time.sleep(0.5)
            pertama_kali = False

        # =====================
        # PASTE NIK
        # =====================
        pg.click(2319, 697)
        time.sleep(1.1)

        pg.rightClick(NIK_BOX_X, NIK_BOX_Y)
        time.sleep(0.8)
        pg.press('p')
        time.sleep(0.3)

        pg.press('tab')
        pg.press('enter')

        if cek_login():
            ulang = True
            continue
        if error_web():
            ulang = True
            continue

        if hapus_data_meninggal():
            ulang = True
            break
        # =====================
        # POPUP (VERSI LEBIH STABIL)
        # =====================
        check_stop_and_exit()
        if cek_login():
            ulang = True
            continue
        if error_web():
            ulang = True
            continue
        print("🔎 Mengecek popup...")

        handle_popup()
        if handle_popup_stuck():    
            ulang = True
            break

        # =====================
        # STUCK PAGE (LEBIH STABIL)
        # =====================
        check_stop_and_exit()
        if cek_login():
            ulang = True
            continue
        if error_web():
            ulang = True
            continue
        print("🔎 Mengecek stuck page...")

        if handle_stuck_page():
            ulang = True
            break

        print("✅ Tidak stuck")

        # =====================
        # TUNGGU CEK PESANAN
        # =====================
        check_stop_and_exit()
        if cek_login():
            ulang = True
            continue
        if error_web():
            ulang = True
            continue    
        time.sleep(0.3)
        if handle_stuck_page():
            ulang = True
            break
        time.sleep(0.3)
        result = wait_with_timeout(
            lambda: warna_mirip(pg.pixel(CEK_X, CEK_Y), CEK_COLOR),
            timeout=10,
            label="CEK PESANAN"
        )
        if result == "STUCK":
            ulang = True
            continue
        time.sleep(0.3)
        if result == "DATA MENINGGAL":
            print("⏭ Skip data meninggal")
            continue
        if result == "TIMEOUT":
            print("❌ Timeout CEK PESANAN → skip")
            winsound.Beep(1500, 700)
            continue
                
        pg.click(CEK_X, CEK_Y)
        time.sleep(0.5)

        pg.press('tab')
        pg.press('enter')

        # =====================
        # CAPTCHA AUTOMATIC SOLVER
        # =====================
        check_stop_and_exit()
        if cek_login():
            ulang = True
            continue
        if error_web():
            ulang = True
            continue
        success = solve_captcha_with_retry()

        if not success:
            if error_web():
                ulang = True
                continue
            winsound.Beep(1500, 700)
            print("❌ CAPTCHA GAGAL TOTAL")
            check_stop_and_exit()
            success = solve_captcha_with_retry()
            if not success:
                print("❌ CAPTCHA RETRY FAILED")
                ulang = True
                continue
        # =====================
        # BACK
        # =====================
        if cek_login():
            ulang = True
            continue
        if error_web():
            ulang = True
            continue
        result = wait_with_timeout(
            lambda: warna_mirip(pg.pixel(BACK_X, BACK_Y), BACK_COLOR),
            timeout=10,
            label="BACK BUTTON"
        )

        if result == "STUCK":
            ulang = True
            continue

        if result == "TIMEOUT":
            print("❌ Timeout BACK")
            continue
        if result == "DATA MENINGGAL":
            print("⏭ Skip data meninggal")
            continue
        
        pg.click(BACK_CLICK_X, BACK_CLICK_Y)
        time.sleep(0.5)

    # =====================
    # BALIK KE WEB
    # =====================
    
    pg.click(CLEAR_COPY_X, CLEAR_COPY_Y) #clear copy
    time.sleep(0.3)
    

print("✅ Completed and Finish!")