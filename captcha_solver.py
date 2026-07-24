import pyautogui as pg
import time
import cv2
import numpy as np
import random
def solve_captcha(region, debug=False):
    time.sleep(0.25)
    screenshot = pg.screenshot(region=region)
    img = cv2.cvtColor(np.array(screenshot), cv2.COLOR_RGB2BGR)
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    # =========================
    # PREPROCESS
    # =========================
    blur = cv2.GaussianBlur(gray, (5,5), 0)

    # edge detection
    edges = cv2.Canny(blur, 50, 150)

    h, w = edges.shape

    # =========================
    # EXTRACT TEMPLATE (LEFT SIDE = PUZZLE PIECE SHAPE)
    # =========================
    template = edges[:, 0:int(w * 0.2)]  # left side usually contains shape

    best_x = 0
    best_score = -1

    # =========================
    # SLIDE TEMPLATE ACROSS IMAGE
    # =========================
    for x in range(int(w * 0.2), w - int(w * 0.2), 3):
        roi = edges[:, x:x + template.shape[1]]

        if roi.shape[1] != template.shape[1]:
            continue

        # template matching score
        result = cv2.matchTemplate(roi, template, cv2.TM_CCOEFF_NORMED)
        score = result[0][0]

        if score > best_score:
            best_score = score
            best_x = x

    if debug:
        print("gap_x:", best_x, "| score:", best_score)

        cv2.rectangle(
            img,
            (best_x, 0),
            (best_x + template.shape[1], h),
            (0,255,0),
            2
        )

        cv2.imshow("debug", img)
        cv2.waitKey(500)
        cv2.destroyAllWindows()

    return best_x + template.shape[1] // 2, best_score
