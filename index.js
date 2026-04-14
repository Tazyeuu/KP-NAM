require('dotenv').config();
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const { GoogleGenAI } = require('@google/genai');

// --- 1. INISIALISASI GEMINI (MESIN BARU) ---
const clientAI = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });

// --- 2. INISIALISASI WHATSAPP ---
const clientWA = new Client({
    authStrategy: new LocalAuth(),
    webVersionCache: {
        type: 'remote',
        remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html',
    },
    puppeteer: {
        headless: true, // Ganti ke false kalau mau lihat browsernya jalan
        handleSIGINT: false, // Penting agar tidak gampang crash saat Ctrl+C
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu',
            '--disable-extensions'
        ],
    }
});

// Munculkan QR Code
clientWA.on('qr', (qr) => {
    console.log('SCAN QR CODE INI, BAL:');
    qrcode.generate(qr, { small: true });
});

clientWA.on('ready', () => {
    console.log('✅ Chatbot RSUD dr. Soedarso (SDK 2026) SIAP!');
});

// --- 3. LOGIKA UTAMA ---
clientWA.on('message', async (msg) => {
    // Filter: Abaikan grup, pesan pendek, atau status
    if (msg.from.includes('@g.us') || msg.body.length < 3 || msg.fromMe) return;

    try {
        console.log(`\n📩 Pesan Masuk: "${msg.body}"`);
        const chat = await msg.getChat();
        await chat.sendStateTyping();

        // Instruksi untuk Gemini (Agar outputnya JSON)
        const systemPrompt = `
        Anda adalah AI Classifier IT Helpdesk RSUD dr. Soedarso.
        Ekstrak keluhan user ke JSON. 
        Kategori: Jaringan, Hardware, Software.
        Format JSON: 
        {
            "is_issue": true/false,
            "category": "Jaringan/Hardware/Software/Unknown",
            "device": "nama alat",
            "summary": "ringkasan singkat"
        }
        HANYA OUTPUT JSON. Tanpa penjelasan tambahan.`;

        // Panggil Gemini (Cara Baru)
        const result = await clientAI.models.generateContent({
            model: 'gemini-2.5-flash',
            contents: [{ role: 'user', parts: [{ text: `${systemPrompt}\n\nUser: "${msg.body}"` }] }]
        });

        // Ambil Teks & Parsing JSON
        const rawText = result.candidates[0].content.parts[0].text;
        const cleanJson = rawText.replace(/```json|```/g, "").trim();
        const data = JSON.parse(cleanJson);

        console.log("🔍 Hasil Klasifikasi:", data);

        // Balasan ke WhatsApp
        if (data.is_issue) {
            await msg.reply(`*Laporan Kendala IT*\n\nKategori: ${data.category}\nPerangkat: ${data.device}\nStatus: Sedang diteruskan ke tim teknis.\n\n_Summary: ${data.summary}_`);
        } else {
            await msg.reply("Halo! Saya asisten IT RSUD Soedarso. Ada kendala teknis yang bisa saya bantu?");
        }

    } catch (error) {
        console.error("❌ Error:", error.message);
        // Jangan reply error ke user agar tidak spam, cukup log di terminal
    }
});

clientWA.initialize();