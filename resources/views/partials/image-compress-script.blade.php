<script>
    // Kompres gambar lewat canvas SEBELUM diunggah - turunkan resolusi lalu
    // re-encode sebagai JPEG, dicoba bertahap (dimensi & kualitas makin
    // turun) sampai lolos target targetBytes. Dipakai di SEMUA tempat upload
    // gambar (thumbnail Input Pesanan, thumbnail Pesanan Jersey, bukti
    // pembayaran) supaya satu-satunya logika kompresi ada di sini - gambar
    // ASLI beresolusi penuh TIDAK PERNAH ikut terkirim ke server ataupun
    // tersimpan di disk manapun (termasuk di komputer development ini),
    // cuma hasil kompresnya. Dimensi ikut diperkecil (bukan cuma kualitas
    // JPEG-nya) karena foto dengan detail tinggi bisa tetap di atas target
    // walau kualitasnya sudah sangat rendah selama dimensinya masih besar.
    // Latar putih digambar dulu sebelum gambar asli supaya PNG transparan
    // tidak berubah jadi hitam saat di-flatten ke JPEG (JPEG tidak punya
    // kanal alpha).
    function compressImage(file, targetBytes = 10 * 1024, dims = [480, 360, 280, 220, 160, 120, 90]) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const objectUrl = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(objectUrl);

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                function renderAt(maxDim, quality) {
                    let { width, height } = img;
                    if (width > maxDim || height > maxDim) {
                        const scale = maxDim / Math.max(width, height);
                        width = Math.round(width * scale);
                        height = Math.round(height * scale);
                    }
                    canvas.width = width;
                    canvas.height = height;
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, width, height);
                    ctx.drawImage(img, 0, 0, width, height);
                    return new Promise((res) => canvas.toBlob(res, 'image/jpeg', quality));
                }

                (async () => {
                    const qualities = [0.6, 0.4, 0.25, 0.15, 0.08];
                    let terkecil = null;

                    for (const dim of dims) {
                        for (const q of qualities) {
                            const blob = await renderAt(dim, q);
                            if (!blob) continue;
                            if (!terkecil || blob.size < terkecil.size) terkecil = blob;
                            if (blob.size <= targetBytes) { resolve(blob); return; }
                        }
                    }
                    // Tidak ada kombinasi yang lolos target (foto sangat kompleks) -
                    // pakai hasil terkecil yang berhasil didapat, jangan sampai
                    // gagal total/tidak ada gambar sama sekali.
                    if (terkecil) resolve(terkecil);
                    else reject(new Error('Gagal memproses gambar.'));
                })();
            };
            img.onerror = () => { URL.revokeObjectURL(objectUrl); reject(new Error('File bukan gambar yang valid.')); };
            img.src = objectUrl;
        });
    }

    // Nama file gambar -> teks deskripsi yang manusiawi: buang ekstensi,
    // ganti underscore/dash dengan spasi, rapikan spasi ganda. "banner_3x1-
    // meter_FINAL.jpg" jadi "banner 3x1 meter FINAL", bukan disalin mentah.
    function namaFileDariGambar(namaAsli) {
        return namaAsli
            .replace(/\.[^./]+$/, '')
            .replace(/[_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }
</script>
