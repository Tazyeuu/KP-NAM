require('dotenv').config();
const { GoogleGenAI } = require('@google/genai');
const readline = require('readline');

const RS_KNOWLEDGE = {
    hardware: ['Printer', 'PC/Komputer', 'Monitor', 'Keyboard', 'Mouse', 'UPS', 'Scanner', 'Mesin Antrean'],
    software: ['SIMRS', 'Windows', 'Microsoft Office', 'Web Browser', 'Antivirus', 'E-Klaim'],
    jaringan: ['WiFi/Hotspot', 'Kabel LAN', 'Router', 'Switch', 'Internet'],
    ruangan: ['Poli Anak', 'Poli Gigi', 'IGD', 'Radiologi', 'Laboratorium', 'Apotek', 'Administrasi']
};

const clientAI = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });
const URL_TIKET_UTAMA = "https://it-helpdesk.soedarso.go.id/tiket"; 

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

const systemPrompt = `
Anda adalah AI IT Helpdesk RSUD dr. Soedarso. Tugas Anda adalah menganalisa pesan staf dan menghasilkan JSON.

PEDOMAN FILTERING:
1. PENTING: Jika pesan user adalah sapaan atau tes (seperti 'p', 'halo', 'tes', 'selamat pagi'), set "is_it_related": true dan "is_issue": false. Jangan arahkan ke Sarpras.
2. is_it_related: Set false HANYA jika keluhan jelas-jelas soal AC, Air, Bangunan, atau Fasilitas Umum non-komputer.
3. priority: "High" jika lokasi 'IGD' atau ada kata 'Gawat/Urgent/Cepat'.

FORMAT OUTPUT JSON:
{
    "is_it_related": true/false,
    "is_issue": true/false,
    "priority": "Normal/High",
    "category": "Jaringan/Hardware/Software/Unknown",
    "device": "Nama perangkat",
    "location": "Nama ruangan",
    "needs_clarification": true/false,
    "confidence_score": 0-100,
    "follow_up_question": "Sapaan balik atau pertanyaan (Bahasa Indonesia Baku)",
    "summary": "Ringkasan singkat"
}
HANYA OUTPUT JSON.`;

async function callGeminiWithRetry(input, retries = 2) {
    for (let i = 0; i <= retries; i++) {
        try {
            const timeoutPromise = new Promise((_, reject) => 
                setTimeout(() => reject(new Error('TIMEOUT')), 15000)
            );

            const apiPromise = clientAI.models.generateContent({
                model: 'gemini-3.1-flash-lite-preview',
                contents: [{ role: 'user', parts: [{ text: `${systemPrompt}\n\nUser: "${input}"` }] }]
            });
            
            const result = await Promise.race([apiPromise, timeoutPromise]);
            return result;
        } catch (err) {
            if (i === retries) throw err;
            console.log(`⚠️ Koneksi lambat/gangguan, mencoba kembali (${i + 1}/${retries})...`);
            await new Promise(res => setTimeout(res, 2000));
        }
    }
}

async function prosesBot(inputUser) {
    try {
        console.log("\n--- [SYSTEM] Sedang memproses keluhan Anda... ---");
        
        const result = await callGeminiWithRetry(inputUser);

        const rawText = result.candidates[0].content.parts[0].text;
        const cleanJson = rawText.replace(/```json|```/g, "").trim();
        const data = JSON.parse(cleanJson);
        
        console.log("✅ ANALISA BERHASIL");

        // --- LOGIKA RESPON BARU (LEBIH ADIL) ---

        // 1. Cek apakah ini benar-benar urusan Sarpras (Non-IT)
        if (data.is_it_related === false) {
            console.log(`\n[BOT]: Mohon maaf, kendala tersebut tampaknya bukan wewenang unit IT. Silakan hubungi bagian Umum atau Sarana Prasarana (Sarpras).`);
        } 
        // 2. Cek apakah ini cuma sapaan, tes, atau info kurang jelas
        else if (data.is_issue === false || data.needs_clarification || data.confidence_score < 60) {
            console.log(`\n[BOT]: ${data.follow_up_question || "Halo! Ada yang bisa kami bantu terkait kendala IT?"}`);
        } 
        // 3. Jika laporan valid dan lengkap
        else {
            const priorityTag = data.priority === 'High' ? '🚨 [PRIORITAS TINGGI]' : '✅';
            console.log(`\n[BOT]: ${priorityTag} Laporan Kendala Dicatat.`);
            console.log(`[BOT]: Deskripsi: ${data.summary}`);
            console.log(`[BOT]: Lokasi: ${data.location}`);
            console.log(`[BOT]: Untuk memantau status perbaikan, silakan cek di: ${URL_TIKET_UTAMA}`);
        }

    } catch (error) {
        console.error(`\n❌ [DEBUG]: ${error.message}`);
        console.log(`\n[BOT]: Mohon maaf, sistem otomatis kami sedang sibuk.`);
        console.log(`[BOT]: Silakan gunakan website utama untuk pelaporan manual: ${URL_TIKET_UTAMA}`);
    }
    tanyaLagi();
}

function tanyaLagi() {
    rl.question('\nKetik pesan (atau "exit"): ', (jawaban) => {
        if (jawaban.toLowerCase() === 'exit') {
            rl.close();
            process.exit();
        }
        prosesBot(jawaban);
    });
}

console.log("=== SIMULATOR IT HELPDESK SOEDARSO (FINAL VERSION) ===");
tanyaLagi();