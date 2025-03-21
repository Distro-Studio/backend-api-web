<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Form Kemenkeu</title>
  <link
    rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Arial:wght@400;700&display=swap" />
  <style>
    * {
      padding: 0;
      margin: 0;
      font-family: "Arial", sans-serif;
      box-sizing: border-box;
    }

    body {
      width: 794px;
      height: 1240px;
    }

    .f4-container {
      width: 794px;
      height: 1240px;
      /* border: 1px solid red; */
      /* padding: 12px; */
    }

    .si {
      background: #000;
      width: 24px;
      height: 10px;
      position: absolute;
    }

    .sis-b {
      background: #000;
      width: 12px;
      height: 12px;
    }

    .sis-w {
      background: #fff;
      border: 1px solid black;
      width: 12px;
      height: 12px;
    }

    .cell {
      margin-top: auto;
      opacity: 0.5;
      font-size: 8px;
      flex-shrink: 0;
    }

    .header-item {
      /* box-shadow: 0 0 0 1px red; */
      position: relative;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 8px;
    }

    .field {
      border-bottom: 1px solid black;
      min-width: 20px;
      min-height: 18.4px;
    }

    .checkbox-field {
      border: 1px solid black;
      min-width: 35px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .checkbox-field-sm {
      border: 1px solid black;
      min-width: 28px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    p,
    th,
    td {
      font-size: 10px;
    }

    table {
      border-collapse: collapse;
      border-spacing: 0;
      width: 100%;
    }

    th,
    td {
      border: 1px solid black !important;
      padding: 4px;
      margin: 0;
      border: 0;
      text-align: left;
      vertical-align: top;
    }

    th {
      text-align: center;
    }

    .rc {
      width: 160px;
    }

    .number-col {
      width: 24px;
      text-align: center;
    }

    .jumlah-col {
      text-align: right;
    }

    .isian {
      font-size: 12px
    }
  </style>
</head>

<body>
  <div style="padding: 12px">
    <div class="f4-container" style="position: relative">
      <!-- Header -->
      <div style="display: flex; border-top: 1px dashed black">
        <div class="header-item" style="width: 27%">
          <div class="si" style="top: 0; left: 0"></div>

          <div
            style="margin-top: auto; margin-bottom: 8px; padding-right: 8px">

            @php
            function convertToBase64($path) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            $logoBase64 = convertToBase64(public_path('kemenkeu/logo_kemenkeu_bnw.png'));
            @endphp

            <img
              src="{{ $logoBase64 }}"
              style="width: 80px; margin-bottom: 12px" />
            <p style="text-align: center; font-weight: bold; font-size: 12px">
              KEMENTRIAN KEUANGAN RI
            </p>
            <p style="text-align: center; font-weight: bold; font-size: 12px">
              DIREKTORAT JENDERAL PAJAK
            </p>
          </div>
        </div>

        <div class="header-item" style="width: 46%; position: relative">
          <div
            style="
                padding: 0 8px;
                border-left: 1px solid black;
                border-right: 1px solid black;
                height: calc(100% - 35.2px);
              ">
            <p style="text-align: center; font-size: 15px; font-weight: bold">
              BUKTI PEMOTONGAN PAJAK PENGHASILAN PASAL 21 BAGI PEGAWAI TETAP
              ATAU PENERIMA PENSIUM ATAU TUNJANGAN HARI TUA/JAMINAN HARI TAU
              BERKALA
            </p>
          </div>

          <div
            style="
                display: flex;
                flex-shrink: 0;
                font-size: 14px;
                gap: 8px;
                width: max-content;
                padding: 10px;
                border-top: 1px solid black;
                border-left: 1px solid black;
                position: absolute;
                bottom: 0;
                left: 0;
                border-right: 1px solid black;
                padding-bottom: 6px;
              ">
            <p style="font-size: 12px; font-weight: bold">NOMOR :</p>
            <p class="cell">H-01</p>
            <p>1</p>
            <p>.</p>
            <p>1</p>
            <p>-</p>
            <div class="field" style="width: 40px">12</div>
            <p>.</p>
            <div class="field" style="width: 40px">20</div>
            <p>-</p>
            <divs class="field" style="width: 125px">0000115</divs>
          </div>
        </div>

        <div class="header-item" style="width: 27%; position: relative">
          <div
            style="
                display: flex;
                flex-direction: column;
                width: 100%;
                margin-top: 5px;
                align-items: start;
                margin-bottom: 60px;
              ">
            <div class="si" style="top: 0; right: 0"></div>

            <div
              style="
                  display: flex;
                  gap: 2px;
                  margin-left: auto;
                  margin-bottom: 8px;
                ">
              <div class="sis-w"></div>
              <div class="sis-b"></div>
              <div class="sis-w"></div>
              <div class="sis-b"></div>
            </div>

            <p
              style="
                  font-weight: bold;
                  margin-left: auto;
                  margin-bottom: 4px;
                  font-size: 16px;
                ">
              FORMULIR 1721 - A1
            </p>

            <div
              style="
                  display: flex;
                  flex-direction: column;
                  align-items: start;
                  margin-left: auto;
                ">
              <p>Lembar ke-1 : untuk Penerima Penghasilan</p>
              <p>Lembar ke-2 : untuk Pemotong</p>
            </div>
          </div>

          <div
            style="
                position: absolute;
                bottom: 0;
                right: 0;
                width: 145px;
                padding: 4px 0px;
              ">
            <p style="font-size: 10px; font-weight: bold">MASA PEROLEHAN</p>
            <p style="font-size: 10px; font-weight: bold; margin-bottom: 4px">
              PENGHASILAN [mm-mm]
            </p>

            <div style="display: flex; gap: 12px">
              <p class="cell">H-02</p>

              <div class="field" style="width: 35px">
                <p class="isian">
                  01
                </p>
              </div>
              <p>-</p>
              <div class="field" style="width: 35px">
                <p class="isian">
                  12
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Info pemotong -->
      <div
        style="
            display: flex;
            flex-direction: column;
            width: 100%;
            border: 1px solid black;
            padding: 8px;
            gap: 4px;
          ">
        <div style="display: flex; align-items: end">
          <p style="font-weight: bold; width: 80px">NPWP PEMOTONG :</p>
          <p class="cell" style="margin-right: 8px">H-03</p>
          <div style="display: flex; gap: 8px">
            <div class="field" style="width: 185px">
              <p class="isian">
                015155302
              </p>
            </div>
            <p>-</p>
            <div class="field" style="width: 60px">
              <p class="isian">
                526
              </p>
            </div>
            <p>.</p>
            <div class="field" style="width: 60px">
              <p class="isian">000</p>
            </div>
          </div>
        </div>

        <div style="display: flex; align-items: end">
          <p style="font-weight: bold; width: 80px">NAMA PEMOTONG :</p>
          <p class="cell" style="margin-right: 8px">H-04</p>
          <div style="display: flex; gap: 8px">
            <div class="field" style="width: 642px">
              <p class="isian">PT KONDANG KASIH IBU</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Identitas Penerima -->
      <p style="font-weight: bold; margin-top: 20px; margin-bottom: 8px">
        A. IDENTITAS PENERIMA PENGHASILAN YANG DIPOTONG
      </p>
      <div
        style="
            display: flex;
            flex-direction: column;
            width: 100%;
            border: 1px solid black;
            padding: 8px;
            gap: 4px;
          ">
        <div style="display: flex; gap: 30px">
          <div
            style="
                width: 55%;
                display: flex;
                flex-direction: column;
                gap: 16px;
              ">
            <div style="display: flex; align-items: end; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">1.</p>
                <p style="font-weight: bold; width: 50px">NPWP</p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <p class="cell" style="margin-right: 8px">A-01</p>
              <div style="display: flex; gap: 8px; width: 100%">
                @php
                  $npwpFormatted = preg_replace('/(\d{9})(\d{3})(\d+)/', '$1 $2 $3', $npwpUser ?? '');
                  $parts = explode(' ', $npwpFormatted);
                @endphp

                @if ($npwpFormatted)
                <div class="field" style="width: 100%">
                  <p class="isian">
                    {{ $parts[0] ?? '' }}
                  </p>
                </div> <!-- 9 angka pertama -->
                <p>-</p>
                <div class="field" style="width: 60%">
                  <p class="isian">
                    {{ $parts[1] ?? '' }}
                  </p>
                </div> <!-- 3 angka berikutnya -->
                <p>.</p>
                <div class="field" style="width: 60%">
                  <p class="isian">
                    {{ $parts[2] ?? '' }}
                  </p>
                </div> <!-- Angka sisanya -->
                @endif
              </div>
            </div>

            <div
              style="
                  display: flex;
                  align-items: center;
                  width: 100%;
                  height: 18.4px;
                ">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">2.</p>
                <p style="font-weight: bold; width: 50px">NIK /NO. PASPOR</p>
              </div>
              <p
                style="font-weight: bold; margin-right: 2px; margin-top: 12px">
                :
              </p>
              <p class="cell" style="margin-right: 8px; margin-top: 16px">
                A-02
              </p>
              <div style="display: flex; gap: 8px; width: 100%">
                <div class="field" style="width: 100%">
                  <p class="isian">
                    {{ $nikUser }}
                  </p>
                </div>
              </div>
            </div>

            <div style="display: flex; align-items: end; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">3.</p>
                <p style="font-weight: bold; width: 50px">NAMA</p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <p class="cell" style="margin-right: 8px">A-03</p>
              <div style="display: flex; gap: 8px; width: 100%">
                <div class="field" style="width: 100%">
                  <p class="isian">
                    {{ $namaUser }}
                  </p>
                </div>
              </div>
            </div>

            <div style="display: flex; align-items: start; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">4.</p>
                <p style="font-weight: bold; width: 50px">ALAMAT</p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <p
                class="cell"
                style="
                    margin-right: 8px;
                    margin-bottom: auto;
                    margin-top: 4px;
                  ">
                A-04
              </p>

              <div
                style="
                    display: flex;
                    flex-direction: column;
                    align-items: stretch;
                    width: 100%;
                  ">
                <div style="display: flex; gap: 8px; width: 100%">
                  <div
                    class="field"
                    style="width: 100%; height: 18.4px">
                    <p class="isian">
                      {{ $alamatUser }}
                    </p>
                  </div>
                </div>

                <div
                  style="
                      display: flex;
                      gap: 8px;
                      width: 100%;
                      margin-top: 16px;
                    ">
                  <div
                    class="field"
                    style="width: 100%; height: 18.4px"></div>
                </div>
              </div>
            </div>

            <div style="display: flex; align-items: center; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">5.</p>
                <p style="font-weight: bold; width: 95px">JENIS KELAMIN</p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <div style="display: flex; align-items: center">
                <p
                  class="cell"
                  style="margin-right: 8px; margin-bottom: auto">
                  A-05
                </p>
                <div style="display: flex; gap: 8px; width: 100%">
                  <div class="checkbox-field">
                    <p class="isian">
                      @if ($jenisKelaminUser === 'LAKI-LAKI')
                      X
                      @endif
                    </p>
                  </div>
                </div>
                <p
                  style="font-weight: bold; flex-shrink: 0; margin-left: 8px">
                  LAKI-LAKI
                </p>
              </div>

              <div
                style="display: flex; align-items: center; margin-left: 12px">
                <p
                  class="cell"
                  style="margin-right: 8px; margin-bottom: auto">
                  A-06
                </p>
                <div style="display: flex; gap: 8px; width: 100%">
                  <div class="checkbox-field">
                    <p class="isian">
                      @if ($jenisKelaminUser === 'PEREMPUAN')
                      X
                      @endif
                    </p>
                  </div>
                </div>
                <p
                  style="font-weight: bold; flex-shrink: 0; margin-left: 8px">
                  PEREMPUAN
                </p>
              </div>
            </div>
          </div>

          <div
            style="
                width: 45%;
                display: flex;
                flex-direction: column;
                gap: 16px;
              ">
            <div style="display: flex; gap: 4px">
              <div>
                <p style="font-weight: bold">6.</p>
              </div>
              <div style="display: flex; flex-direction: column; width: 100%">
                <div style="display: flex; align-items: start; gap: 4px">
                  <p style="font-weight: bold">
                    STATUS / JUMLAH TANGGUNGAN KELUARGA UNTUK PTKP
                  </p>
                </div>

                <div style="display: flex; gap: 20px">
                  <div
                    style="
                        display: flex;
                        gap: 4px;
                        align-items: center;
                        margin-top: 8px;
                        /* margin-bottom: 8px ; */
                      ">
                    <p style="font-weight: bold">K /</p>
                    <div style="display: flex; gap: 8px">
                      <div class="field" style="width: 30px">
                        @if (Str::startsWith($jenisTanggunganUser ?? '', 'K/'))
                        {{ explode('/', $jenisTanggunganUser)[1] ?? '' }}
                        @endif
                      </div>
                    </div>
                    <p
                      class="cell"
                      style="margin-right: 8px; margin-top: 16px">
                      A-07
                    </p>
                  </div>

                  <div
                    style="
                        display: flex;
                        gap: 4px;
                        align-items: center;
                        margin-top: 8px;
                        /* margin-bottom: 8px ; */
                      ">
                    <p style="font-weight: bold">TK /</p>
                    <div style="display: flex; gap: 8px">
                      <div class="field" style="width: 30px">
                        <p class="isian">
                          @if (Str::startsWith($jenisTanggunganUser ?? '', 'TK/'))
                          {{ explode('/', $jenisTanggunganUser)[1] ?? '' }}
                          @endif
                        </p>
                      </div>
                    </div>
                    <p
                      class="cell"
                      style="margin-right: 8px; margin-top: 16px">
                      A-08
                    </p>
                  </div>

                  <div
                    style="
                        display: flex;
                        gap: 4px;
                        align-items: center;
                        margin-top: 8px;
                        /* margin-bottom: 8px ; */
                      ">
                    <p style="font-weight: bold">HB /</p>
                    <div style="display: flex; gap: 8px">
                      <div class="field" style="width: 30px">
                        <p class="isian">
                          @if (Str::startsWith($jenisTanggunganUser ?? '', 'HB/'))
                          {{ explode('/', $jenisTanggunganUser)[1] ?? '' }}
                          @endif
                        </p>
                      </div>
                    </div>
                    <p
                      class="cell"
                      style="margin-right: 8px; margin-top: 16px">
                      A-09
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div style="display: flex; align-items: end; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">7.</p>
                <p style="font-weight: bold; width: 84px">NAMA JABATAN</p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <p class="cell" style="margin-right: 8px">A-10</p>
              <div style="display: flex; gap: 8px; width: 100%">
                <div class="field" style="width: 100%">
                  <p class="isian">
                    {{ $jabatanUser }}
                  </p>
                </div>
              </div>
            </div>

            <div style="display: flex; align-items: center; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">8.</p>
                <p style="font-weight: bold; width: 95px">KARYAWAN ASING</p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <div style="display: flex; align-items: center">
                <p
                  class="cell"
                  style="
                      margin-left: 40px;
                      margin-right: 8px;
                      margin-bottom: auto;
                    ">
                  A-11
                </p>
                <div style="display: flex; gap: 8px; width: 100%">
                  <div class="checkbox-field">
                    <!-- // TODO karyawan asing -->
                  </div>
                </div>
                <p
                  style="font-weight: bold; flex-shrink: 0; margin-left: 8px">
                  YA
                </p>
              </div>
            </div>

            <div style="display: flex; align-items: end; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">9.</p>
                <p style="font-weight: bold; width: 124px">
                  KODE NEGARA DOMISILI
                </p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <p class="cell" style="margin-right: 8px">A-12</p>
              <div style="display: flex; gap: 8px; width: 100%">
                <div class="field" style="width: 100%">
                  <!-- // TODO Kode negara domisili belum dihandle -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Rincian Penghasilan -->
      <p style="font-weight: bold; margin-top: 20px; margin-bottom: 8px">
        B. RINCIAN PENGHASILAN DAN PENGHITUNGAN PPh PASAL 21
      </p>
      <table>
        <thead>
          <tr>
            <th colspan="2">URAIAN</th>
            <th class="rc">Jumlah (Rp)</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan="2">
              <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    height: 11.2px;
                  ">
                <p style="font-weight: bold">KODE OBJEK PAJAK :</p>
                <div class="checkbox-field-sm">
                </div>
                <p>21-100-01</p>
                <div class="checkbox-field-sm">
                  X
                </div>
                <p>21-100-02</p>
              </div>
            </td>
            <td bgcolor="gray"></td>
          </tr>

          <tr>
            <td colspan="2">
              <div style="display: flex; align-items: center; gap: 8px">
                <p style="font-weight: bold">PENGHASILAN BRUTO :</p>
              </div>
            </td>
            <td bgcolor="gray"></td>
          </tr>

          <tr>
            <td class="number-col">1.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>GAJI/PENSIUN ATAU THT/JHT</p>
              </div>
            </td>
            <td class="jumlah-col">{{ $gajiPokok }}</td>
          </tr>

          <tr>
            <td class="number-col">2.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>TUNJANGAN PPh</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">3.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>TUNJANGAN LAINNYA, UANG LEMBUR DAN SEBAGAINYA</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">4.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>HONORARIUM DAN IMBALAN LAIN SEJENISNYA</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">5.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>PREMI ASURANSI YANG DIBAYAR PEMBERI KERJA</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">6.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>
                  PENERIMAAN DALAM BENTUK NATURA DAN KENIKMATAN LAINNYA YANG
                  DIKENAKAN PEMOTONGAN PPh PASAL 21
                </p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">7.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>TANTEM, BONUS, GRATIFIKASI, JASA PRODUKSI DAN THR</p>
              </div>
            </td>
            <td class="jumlah-col">{{ $tambahanTHRPresensi }}</td>
          </tr>

          <tr>
            <td class="number-col">8.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>JUMLAH PENGHASILAN BRUTO (1 S.D 7)</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td colspan="2">
              <div style="display: flex; align-items: center; gap: 8px">
                <p style="font-weight: bold">PENGURANGAN</p>
              </div>
            </td>
            <td bgcolor="gray"></td>
          </tr>

          <tr>
            <td class="number-col">9.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>BIAYA JABATAN/BIAYA PENSIUN</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">10.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>IURAN PENSIUN ATAU IURAN THT/JHT</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">11.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>JUMLAH PENGURANGAN (9 S.D 10)</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td colspan="2">
              <div style="display: flex; align-items: center; gap: 8px">
                <p style="font-weight: bold">PENGHITUNGAN PPh PASAL 21</p>
              </div>
            </td>
            <td bgcolor="gray"></td>
          </tr>

          <tr>
            <td class="number-col">12.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>JUMLAH PENGHASILAN NETO (8 - 11)</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">13.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>PENGHASILAN NETO MASA SEBELUMNYA</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">14.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>
                  JUMLAH PENGHASILAN NETO UNTUK PERHITUNGAN PPh PASAL 21
                  (SETAHUN/DISETAHUNKAN)
                </p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">15.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>PENGHASILAN TIDAK KENA PAJAK (PTKP)</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">16.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>PENGHASILAN KENA PAJAK SETAHUN/DISETAHUNKAN (14 - 15)</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">17.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>
                  PPh PASAL 21 ATAS PENGHASILAN KENA PAJAK
                  SETAHUN/DISETAHUNKAN
                </p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">18.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>PPh PASAL 21 YANG TELAH DIPOTONG MASA SEBELUMNYA</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">19.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>PPh PASAL 21 TERUTANG</p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>

          <tr>
            <td class="number-col">20.</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px">
                <p>
                  PPh PASAL 21 DAN PPh PASAL 26 YANG TELAH DIPOTONG DAN
                  DILUNASI
                </p>
              </div>
            </td>
            <td class="jumlah-col"></td>
          </tr>
        </tbody>
      </table>

      <!-- Identitas Pemotong -->
      <p style="font-weight: bold; margin-top: 20px; margin-bottom: 8px">
        C. IDENTITAS PEMOTONG
      </p>
      <div
        style="
            display: flex;
            flex-direction: column;
            width: 100%;
            border: 1px solid black;
            padding: 4px;
            padding-left: 8px;
            gap: 4px;
          ">
        <div style="display: flex; gap: 30px">
          <div
            style="
                width: 55%;
                display: flex;
                flex-direction: column;
                gap: 16px;
              ">
            <div style="display: flex; align-items: end; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">1.</p>
                <p style="font-weight: bold; width: 50px">NPWP</p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <p class="cell" style="margin-right: 8px">C-01</p>
              <div style="display: flex; gap: 8px; width: 100%">
                <div class="field" style="width: 100%">
                  <p class="isian">
                    015155302
                  </p>
                </div>
                <p>-</p>
                <div class="field" style="width: 60px">
                  <p class="isian">
                    526
                  </p>
                </div>
                <p>.</p>
                <div class="field" style="width: 60px">
                  <p class="isian">
                    000
                  </p>
                </div>
              </div>
            </div>

            <div style="display: flex; align-items: end; width: 100%">
              <div style="display: flex; align-items: start; gap: 4px">
                <p style="font-weight: bold">2.</p>
                <p style="font-weight: bold; width: 50px">NAMA</p>
              </div>
              <p style="font-weight: bold; margin-right: 2px">:</p>
              <p class="cell" style="margin-right: 8px">C-02</p>
              <div style="display: flex; gap: 8px; width: 100%">
                <div class="field" style="width: 100%">
                  <p class="isian">
                    PT KONDANG SEHAT KASIH IBU
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div style="width: 45%; display: flex; gap: 4px">
            <div style="display: flex; gap: 4px; margin-top: 4px">
              <div>
                <p style="font-weight: bold">3.</p>
              </div>
              <div
                style="
                    display: flex;
                    flex-direction: column;
                    width: 100%;
                    position: relative;
                  ">
                <div style="display: flex; align-items: start; gap: 4px">
                  <p style="font-weight: bold">TANGGAL & TANDA TANGAN</p>
                </div>

                <div style="display: flex; gap: 8px; align-items: end">
                  <div class="field" style="width: 30px">
                    <!-- // TODO tanggal belum dihandle -->
                  </div>

                  <p>-</p>

                  <div class="field" style="width: 30px"></div>

                  <p>-</p>

                  <div class="field" style="width: 50px"></div>
                </div>

                <p
                  style="
                      font-weight: bold;
                      margin-left: 12px;
                      margin-top: 4px;
                    ">
                  [dd-mm-yyyy]
                </p>
              </div>
            </div>

            <div
              style="
                  border: 1px solid black;
                  width: 160px;
                  height: 70px;
                  margin-left: auto;
                "></div>
          </div>
        </div>
      </div>

      <div class="si" style="bottom: 0; left: 0"></div>
      <div class="si" style="bottom: 0; right: 0"></div>
    </div>
  </div>
</body>

</html>