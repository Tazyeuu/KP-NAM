require('dotenv').config();
const { GoogleGenAI } = require('@google/genai');
const readline = require('readline');

// 1. DATABASE SOLUSI MANDIRI (Knowledge Base)
const TROUBLESHOOTING_DB = {
    "Printer": [
        {
            masalah: "Printer tidak mau mencetak (Macet/Error)",
            solusi: "1. Cek apakah ada kertas yang tersangkut (Paper Jam).\n2. Pastikan baki kertas terisi dan tertutup rapat.\n3. Matikan printer, tunggu 10 detik, lalu nyalakan kembali."
        },
        {
            masalah: "Printer tidak bisa hidup (Mati Total)",
            solusi: "1. Pastikan kabel power sudah tertancap kuat di stopkontak.\n2. Coba tekan tombol power selama 3 detik.\n3. Cek apakah saklar listrik di ruangan tersebut menyala."
        },
        {
            masalah: "Printer tidak terhubung dengan laptop/PC",
            solusi: "1. Cabut dan colok kembali kabel USB printer ke laptop.\n2. Pastikan Anda memilih printer yang benar di menu 'Print'.\n3. Restart laptop Anda jika koneksi masih belum terdeteksi."
        }
    ],
    "WiFi/Hotspot": [
        {
            masalah: "Tidak bisa terhubung ke WiFi",
            solusi: "1. Matikan WiFi di perangkat Anda, tunggu 5 detik, lalu nyalakan kembali.\n2. Klik 'Forget Network' lalu coba masukkan kembali password.\n3. Pastikan Anda berada di area yang terjangkau sinyal WiFi."
        }
    ]
    // Tambahkan aset lain di sini nantinya
};

const clientAI = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });
const URL_TIKET_UTAMA = "https://it-helpdesk.soedarso.go.id/tiket"; 

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

// State untuk melacak apakah user sedang dalam menu troubleshooting
let sessionState = {
    inTroubleshooting: false,
    currentDevice: null,
    lastData: null
};

const systemPrompt = `
Anda adalah AI IT Helpdesk RSUD dr. Soedarso.
Tugas: Klasifikasi pesan user ke JSON.

ATURAN KETAT:
1. IS_IT_RELATED: 
   - Set TRUE untuk: Sapaan (P, Halo, Hai), tes koneksi, dan keluhan IT (Printer, PC, WiFi, Software).
   - Set FALSE HANYA untuk: Keluhan fisik bangunan (WC sumbat, AC panas, Lampu mati, Atap bocor).
2. NEEDS_CLARIFICATION:
   - Set TRUE jika user bilang "ada yang rusak" tapi TIDAK menyebutkan perangkatnya apa.
   - Jika TRUE, buat pertanyaan di "follow_up_question" (Contoh: "Mohon maaf, perangkat apa yang rusak?").

FORMAT JSON:
{
    "is_it_related": true/false,
    "is_issue": true/false,
    "device": "Nama perangkat atau 'Unknown'",
    "needs_clarification": true/false,
    "follow_up_question": "Kalimat tanya/sapaan",
    "summary": "Ringkasan"
}
HANYA OUTPUT JSON.`;

const MODEL_FALLBACK_LIST = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-flash-latest'];

async function callGeminiWithRetry(input) {
    let lastError = null;
    for (const modelName of MODEL_FALLBACK_LIST) {
        for (let attempt = 1; attempt <= 2; attempt++) {
            try {
                const timeoutPromise = new Promise((_, reject) => setTimeout(() => reject(new Error('TIMEOUT')), 12000));
                const apiPromise = clientAI.models.generateContent({
                    model: modelName,
                    contents: [{ role: 'user', parts: [{ text: `${systemPrompt}\n\nUser: "${input}"` }] }]
                });
                const result = await Promise.race([apiPromise, timeoutPromise]);
                return result;
            } catch (err) {
                lastError = err;
                if (err.message.includes('429')) break;
                await new Promise(res => setTimeout(res, 1500));
            }
        }
    }
    throw lastError;
}

async function prosesBot(inputUser) {
    // A. JIKA USER SEDANG DALAM MENU TROUBLESHOOTING
    if (sessionState.inTroubleshooting) {
        const choice = parseInt(inputUser);
        const options = TROUBLESHOOTING_DB[sessionState.currentDevice];

        // 1. Jika user memilih angka yang VALID
        if (!isNaN(choice) && choice >= 1 && choice <= options.length) {
            const selected = options[choice - 1];
            console.log(`\n[BOT]: Baiklah, silakan coba langkah berikut untuk "${selected.masalah}":`);
            console.log(`\n${selected.solusi}`);
            console.log(`\n[BOT]: Apakah sudah berhasil? Jika belum, silakan isi detail lainnya di sini: ${URL_TIKET_UTAMA}`);
            
            sessionState.inTroubleshooting = false; // Selesai
            tanyaLagi();
            return;
        } 
        // 2. Jika user memilih angka terakhir (Masalah Tidak Ada di Daftar)
        else if (choice === options.length + 1) {
            console.log(`\n[BOT]: Baik, laporan Anda akan diteruskan ke teknisi. Silakan isi detail selengkapnya di: ${URL_TIKET_UTAMA}`);
            sessionState.inTroubleshooting = false;
            tanyaLagi();
            return;
        }
        // 3. JIKA USER MENGETIK TEKS (Bukan Angka)
        else if (isNaN(choice)) {
            console.log(`\n[BOT]: Mengerti. Saya akan mencatat detail tambahan tersebut dan meneruskannya ke tim teknisi.`);
            sessionState.inTroubleshooting = false; // Keluar dari mode menu
            // Kita lanjut ke proses analisa AI di bawah (B) menggunakan inputUser yang baru
        } 
        else {
            console.log(`\n[BOT]: Mohon masukkan angka yang sesuai (1-${options.length + 1}).`);
            tanyaLagi();
            return;
        }
    }

    // B. PROSES ANALISA (AI)
    try {
        console.log("\n--- [SYSTEM] Menganalisa... ---");
        const result = await callGeminiWithRetry(inputUser);
        const data = JSON.parse(result.candidates[0].content.parts[0].text.replace(/```json|```/g, "").trim());

        if (data.is_it_related === false) {
            console.log(`\n[BOT]: Mohon maaf, keluhan tersebut (fasilitas umum/bangunan) bukan wewenang IT. Silakan hubungi bagian Sarpras.`);
        } 
        // 2. Cek Sapaan (Bukan Masalah)
        else if (data.is_issue === false) {
            console.log(`\n[BOT]: ${data.follow_up_question || "Halo! Ada yang bisa kami bantu terkait IT?"}`);
        }
        // 3. Cek apakah infonya "Gantung" (Butuh Tanya Balik)
        else if (data.needs_clarification || data.device === "Unknown") {
            console.log(`\n[BOT]: ${data.follow_up_question || "Bisa diinfokan perangkat apa yang bermasalah agar kami bisa membantu?"}`);
        }
        // 4. Cek apakah ada di Menu Solusi
        else if (TROUBLESHOOTING_DB[data.device]) {
            sessionState.inTroubleshooting = true;
            sessionState.currentDevice = data.device;
            console.log(`\n[BOT]: Saya mendeteksi masalah pada ${data.device}.`);
            console.log(`[BOT]: Silakan pilih kendala berikut atau ketik detail tambahan:`);
            TROUBLESHOOTING_DB[data.device].forEach((item, index) => console.log(`${index + 1}. ${item.masalah}`));
            console.log(`${TROUBLESHOOTING_DB[data.device].length + 1}. Masalah tidak ada di daftar.`);
        }
        // 5. Jika semua jelas tapi tidak ada di menu solusi
        else {
            console.log(`\n[BOT]: Laporan ${data.device} telah diterima. Mohon lengkapi di: ${URL_TIKET_UTAMA}`);
        }

    } catch (error) {
        console.log(`\n[BOT]: Sistem sibuk, silakan lapor di: ${URL_TIKET_UTAMA}`);
    }
    tanyaLagi();
}
        
function tanyaLagi() {
    const promptText = sessionState.inTroubleshooting ? 'Pilih nomor: ' : '\nKetik pesan (atau "exit"): ';
    rl.question(promptText, (jawaban) => {
        if (jawaban.toLowerCase() === 'exit') {
            rl.close();
            process.exit();
        }
        prosesBot(jawaban);
    });
}

console.log("=== SIMULATOR IT HELPDESK SOEDARSO (SELF-SERVICE EDITION) ===");
tanyaLagi();