<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <title>Sertifikat</title>
        <style>
            @page {
                size: A4 landscape;
                margin: 0;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                height: 100%;
            }

            body {
                font-family: "Book Antiqua", serif;
                position: relative;
            }

            /* Background full-page */
            .bg-image {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                z-index: 0;
            }

            /* Area teks di sisi kanan (hindari strip kiri) */
            .content {
                position: relative;
                z-index: 1;
                padding-top: 40mm;
                padding-left: 46mm;
                padding-right: 25mm;
                padding-bottom: 25mm;
                display: flex;
                flex-direction: column;
                color: #000;
            }

            .text-block {
                font-size: 14pt;
                line-height: 1.5;
            }

            .label {
                margin: 0 0 2mm 0;
            }

            .participant-name {
                font-size: 22pt;
                font-weight: 700;
                text-transform: uppercase;
                margin: 2mm 0 8mm 0;
            }

            .role {
                font-size: 20pt;
                font-weight: 700;
            }

            .section-space {
                margin-top: 6mm;
            }

            .diklat-title {
                font-size: 20pt;
                font-weight: 700;
                text-align: center;
                margin: 14mm 0 6mm 0;
            }

            .place-date {
                font-size: 11pt;
                text-align: center;
                margin-top: 2mm;
            }
        </style>
    </head>

    <body>
        <!-- Background image: pakai public_path agar DomPDF bisa baca -->
        <img
            src="{{ public_path('mails/images/bg-diklat-certificate.jpg') }}"
            class="bg-image"
        />

        <!-- Konten sertifikat -->
        <div class="content">
            <div class="text-block">
                <p class="label">Diberikan Kepada :</p>
                <p class="participant-name">{{ strtoupper($user->nama) }}</p>

                <p class="label">
                    Atas Partisipasinya Sebagai :
                    <span class="role">PESERTA</span>
                </p>

                <p class="label section-space">Dalam Kegiatan :</p>
                <p class="diklat-title">{{ $diklat->nama }}</p>

                <p class="place-date">
                    RS Kasih Ibu Surakarta, {{
                    \Carbon\Carbon::parse($diklat->tgl_mulai)->locale('id')->isoFormat('D
                    MMMM YYYY') }}
                </p>
            </div>
        </div>
    </body>
</html>
