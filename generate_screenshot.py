import struct, zlib

path = r'C:\xampp12\htdocs\bobbyafrica\wp-content\themes\bobbyafrica-marketplace-child\screenshot.png'
width, height = 1200, 900
rows = []
for y in range(height):
    row = bytearray([0])
    for x in range(width):
        if x < width * 0.42:
            r, g, b = 245, 247, 250
        elif x > width * 0.60 and y < height * 0.7:
            r, g, b = 255, 255, 255
        else:
            r, g, b = 18, 59, 74
        if y < height * 0.36:
            r, g, b = 15, 118, 110
        row.extend((r, g, b, 255))
    rows.append(bytes(row))
raw = b''.join(rows)

def chunk(tag, data):
    return struct.pack('!I', len(data)) + tag + data + struct.pack('!I', zlib.crc32(tag + data) & 0xffffffff)

image = (
    b'\x89PNG\r\n\x1a\n'
    + chunk(b'IHDR', struct.pack('!IIBBBBB', width, height, 8, 6, 0, 0, 0))
    + chunk(b'IDAT', zlib.compress(raw, 9))
    + chunk(b'IEND', b'')
)

with open(path, 'wb') as f:
    f.write(image)

print(path)
