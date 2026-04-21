require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { GoogleGenAI } = require('@google/genai');

const app = express();
app.use(cors());
app.use(express.json());

const clientAI = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });
const URL_TIKET_UTAMA = "https://it-helpdesk.soedarso.go.id/tiket"; 

// 1. DATABASE SOLUSI (Identik dengan simulator.js)
const TROUBLESHOOTING_DB = {
    "Printer": [
        { masalah: "Printer tidak mau mencetak (Macet/Error)", solusi: "1. Cek apakah ada kertas yang tersangkut (Paper Jam).\n2. Pastikan baki kertas terisi dan tertutup rapat.\n3. Matikan printer, tunggu 10 detik, lalu nyalakan kembali." },
        { masalah: "Printer tidak bisa hidup (Mati Total)", solusi: "1. Pastikan kabel power sudah tertancap kuat di stopkontak.\n2. Coba tekan tombol power selama 3 detik.\n3. Cek apakah saklar listrik di ruangan tersebut menyala." },
        { masalah: "Printer tidak terhubung dengan laptop/PC", solusi: "1. Cabut dan colok kembali kabel USB printer ke laptop.\n2. Pastikan Anda memilih printer yang benar di menu 'Print'.\n3. Restart laptop Anda jika koneksi masih belum terdeteksi." }
    ],
    "WiFi/Hotspot": [
        { masalah: "Tidak bisa terhubung ke WiFi", solusi: "1. Matikan WiFi di perangkat Anda, tunggu 5 detik, lalu nyalakan kembali.\n2. Klik 'Forget Network' lalu coba masukkan kembali password.\n3. Pastikan Anda berada di area yang terjangkau sinyal WiFi." }
    ]
};

// 2. SYSTEM PROMPT (Identik dengan simulator.js)
const systemPrompt = `
Anda adalah AI IT Helpdesk RSUD dr. Soedarso.
TUGAS: Ekstrak pesan user ke JSON.

ATURAN WAJIB:
1. Jika user menyebutkan kata 'Printer', 'Komputer', atau 'WiFi', Anda WAJIB mengeset "device" sesuai nama alat tersebut, meskipun user tidak menyebutkan kerusakannya secara detail.
2. Jangan set "needs_clarification": true jika Anda sudah tahu perangkat apa yang dimaksud. Prioritaskan identifikasi perangkat.
3. Lokasi "Unknown" tidak masalah, jangan bertanya soal lokasi jika Anda sudah tahu perangkatnya.

FORMAT JSON:
{
    "is_it_related": true,
    "is_issue": true,
    "device": "Printer", // Contoh
    "needs_clarification": false,
    "follow_up_question": "",
    "summary": "Laporan kerusakan printer"
}
HANYA OUTPUT JSON.`;

const MODEL_FALLBACK_LIST = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-flash-latest'];

// 3. FUNGSI AI DENGAN RETRY & FALLBACK (Sama dengan simulator.js)
async function callGeminiWithRetry(input) {
    let lastError = null;
    for (const modelName of MODEL_FALLBACK_LIST) {
        for (let attempt = 1; attempt <= 2; attempt++) {
            try {
                const timeoutPromise = new Promise((_, reject) => setTimeout(() => reject(new Error('TIMEOUT')), 12000));
                const apiPromise = clientAI.models.generateContent({
                    model: modelName,
                    contents: [{ role: 'user', parts: [{ text: `${systemPrompt}\n\nUser: "${input}"` }] }],
                    generationConfig: {
                        temperature: 0.1, // Semakin mendekati 0, AI akan semakin kaku/konsisten (tidak ngasal)
                    }
                });
                const result = await Promise.race([apiPromise, timeoutPromise]);
                const rawText = result.candidates[0].content.parts[0].text;
                return JSON.parse(rawText.replace(/```json|```/g, "").trim());
            } catch (err) {
                lastError = err;
                if (err.message.includes('429')) break;
                await new Promise(res => setTimeout(res, 1500));
            }
        }
    }
    throw lastError;
}

// Memory session untuk user website
let webSessions = {};

// --- ENDPOINT API ---
app.post('/api/chat', async (req, res) => {
    const { message, sessionId } = req.body;
    if (!webSessions[sessionId]) webSessions[sessionId] = { inTroubleshooting: false, currentDevice: null };
    let state = webSessions[sessionId];

    try {
        // A. JIKA DALAM MENU TROUBLESHOOTING (Logika Angka vs Teks)
        if (state.inTroubleshooting) {
            const choice = parseInt(message);
            const options = TROUBLESHOOTING_DB[state.currentDevice];

            if (!isNaN(choice) && choice >= 1 && choice <= options.length) {
                const selected = options[choice - 1];
                state.inTroubleshooting = false;
                return res.json({ reply: `Silakan coba: ${selected.solusi}\n\nApakah berhasil? Jika tidak, isi tiket di: ${URL_TIKET_UTAMA}` });
            } else if (choice === options.length + 1) {
                state.inTroubleshooting = false;
                return res.json({ reply: `Baik, silakan isi laporan selengkapnya di: ${URL_TIKET_UTAMA}` });
            } else if (isNaN(choice)) {
                state.inTroubleshooting = false; // User interupsi pakai teks, lanjut analisa AI
            } else {
                return res.json({ reply: `Mohon pilih nomor 1-${options.length + 1}.` });
            }
        }

        // B. PROSES ANALISA AI
        const data = await callGeminiWithRetry(message);

        if (data.is_it_related === false) {
            return res.json({ reply: "Mohon maaf, kendala tersebut bukan wewenang IT (Sarpras).", type: 'rejection' });
        } 
        
        if (data.is_issue === false) {
            return res.json({ reply: data.follow_up_question || "Halo! Ada yang bisa dibantu?" });
        }

        if (data.needs_clarification || data.device === "Unknown") {
            return res.json({ reply: data.follow_up_question || "Perangkat apa yang bermasalah?" });
        }

        if (TROUBLESHOOTING_DB[data.device]) {
            state.inTroubleshooting = true;
            state.currentDevice = data.device;
            let menu = TROUBLESHOOTING_DB[data.device].map((item, i) => `${i+1}. ${item.masalah}`).join('\n');
            return res.json({ 
                reply: `Deteksi: ${data.device}. Pilih kendala:\n${menu}\n${TROUBLESHOOTING_DB[data.device].length + 1}. Lainnya (Buat Tiket)`,
                type: 'menu'
            });
        }

        res.json({ reply: `Laporan ${data.device} diterima. Silakan lengkapi di: ${URL_TIKET_UTAMA}` });

    } catch (error) {
        res.status(500).json({ reply: "Sistem sibuk, silakan gunakan link manual: " + URL_TIKET_UTAMA });
    }
});

const PORT = 3000;
app.listen(PORT, () => console.log(`🚀 API Server Berjalan di http://localhost:${PORT}`));