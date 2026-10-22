(function () {
    const input = document.querySelector('[data-menu-image]');

    if (!input) {
        return;
    }

    const preview = document.querySelector('[data-image-preview]');
    const previewImage = document.querySelector('[data-image-preview-img]');
    const info = document.querySelector('[data-image-info]');
    const submit = document.querySelector('[data-menu-submit]');

    const MAX_DIMENSION = 1200;
    const MAX_UPLOAD_BYTES = 2 * 1024 * 1024;
    const MAX_INPUT_BYTES = 20 * 1024 * 1024;

    let previewUrl = null;

    function showPreview(blob) {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        previewUrl = URL.createObjectURL(blob);

        if (previewImage) {
            previewImage.src = previewUrl;
        }

        if (preview) {
            preview.hidden = false;
        }
    }

    function clearPreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        if (previewImage) {
            previewImage.removeAttribute('src');
        }

        if (preview) {
            preview.hidden = true;
        }
    }

    function setInfo(message) {
        if (info) {
            info.textContent = message;
        }
    }

    function setBusy(busy) {
        if (submit) {
            submit.disabled = busy;
        }
    }

    function formatSize(bytes) {
        if (bytes >= 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        return Math.round(bytes / 1024) + ' KB';
    }

    async function loadImage(file) {
        if ('createImageBitmap' in window) {
            try {
                return await createImageBitmap(file, { imageOrientation: 'from-image' });
            } catch (error) {
                // Fall through to the <img> fallback below.
            }
        }

        return new Promise(function (resolve, reject) {
            const image = new Image();
            const url = URL.createObjectURL(file);

            image.onload = function () {
                URL.revokeObjectURL(url);
                resolve(image);
            };

            image.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Gambar tidak bisa dibaca.'));
            };

            image.src = url;
        });
    }

    async function compress(file) {
        const image = await loadImage(file);
        const width = image.width || image.naturalWidth;
        const height = image.height || image.naturalHeight;
        const scale = Math.min(1, MAX_DIMENSION / width, MAX_DIMENSION / height);

        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(width * scale));
        canvas.height = Math.max(1, Math.round(height * scale));

        const context = canvas.getContext('2d');

        if (!context) {
            if (typeof image.close === 'function') {
                image.close();
            }

            throw new Error('Canvas tidak didukung.');
        }

        try {
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
        } finally {
            if (typeof image.close === 'function') {
                image.close();
            }
        }

        const blob = await new Promise(function (resolve) {
            canvas.toBlob(resolve, 'image/webp', 0.82);
        });

        if (!blob) {
            return null;
        }

        return blob;
    }

    input.addEventListener('change', async function () {
        const file = input.files && input.files[0];

        if (!file) {
            clearPreview();
            return;
        }

        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
            setInfo('Format harus JPG, PNG, atau WebP.');
            input.value = '';
            clearPreview();
            return;
        }

        if (file.size > MAX_INPUT_BYTES) {
            setInfo('File terlalu besar. Pilih foto di bawah 20MB.');
            input.value = '';
            clearPreview();
            return;
        }

        if (typeof DataTransfer === 'undefined') {
            if (file.size > MAX_UPLOAD_BYTES) {
                setInfo('Browser tidak mendukung kompresi otomatis. Pilih foto di bawah 2MB.');
                input.value = '';
                clearPreview();
                return;
            }

            showPreview(file);
            setInfo('Siap diupload: ' + formatSize(file.size) + ' (file asli).');
            return;
        }

        setBusy(true);
        setInfo('Mengompres foto...');

        try {
            const blob = await compress(file);

            if (!blob) {
                throw new Error('Kompresi tidak didukung.');
            }

            if (blob.size > MAX_UPLOAD_BYTES) {
                throw new Error('Hasil kompresi masih terlalu besar.');
            }

            const extension = blob.type === 'image/webp'
                ? 'webp'
                : (blob.type === 'image/png' ? 'png' : 'jpg');
            const compressed = new File([blob], 'menu.' + extension, {
                type: blob.type,
                lastModified: Date.now(),
            });

            const transfer = new DataTransfer();
            transfer.items.add(compressed);
            input.files = transfer.files;

            showPreview(blob);
            setInfo('Siap diupload: ' + formatSize(blob.size) + ' (otomatis dikompres).');
        } catch (error) {
            if (file.size > MAX_UPLOAD_BYTES) {
                input.value = '';
                clearPreview();
                setInfo('Foto lebih dari 2MB dan gagal dikompres. Pilih foto lain.');
            } else {
                showPreview(file);
                setInfo('Kompresi tidak tersedia. File asli tetap dipakai (' + formatSize(file.size) + ').');
            }
        } finally {
            setBusy(false);
        }
    });
})();
