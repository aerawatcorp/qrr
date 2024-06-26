import pathlib
import qrcode

from datetime import datetime
from hashlib import md5

from fastapi import FastAPI
from fastapi.responses import FileResponse


app = FastAPI()


@app.get("/")
def generate_qr(chl: str):
    date = datetime.now().strftime("%Y/%m/%d")
    chl_hash = md5(chl.encode()).hexdigest()

    cache_path = pathlib.Path("cache") / date
    cache_path.mkdir(parents=True, exist_ok=True)

    file_name = f"{chl_hash}.png"
    headers = {"X-QRR-Hash": chl_hash, "X-QRR-Cache": "HIT"}

    img_path = cache_path / file_name

    if not img_path.exists():
        headers["X-QRR-Cache"] = "MISS"

        qr = qrcode.QRCode(
            box_size=10,
            border=4,
        )
        qr.add_data(chl)
        qr.make(fit=True)
        img = qr.make_image(back_color="transparent")
        img.save(str(img_path))

    return FileResponse(path=img_path, media_type="image/png", headers=headers)
