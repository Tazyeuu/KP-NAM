require('dotenv').config(); // Load API Key dari .env
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const { GoogleGenerativeAI } = require("@google/generative-ai");

// --- 1. INISIALISASI GEMINI ---
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const model = genAI.getGenerativeModel({ model: "gemini-2.0-flash"});
    
// --- 2. INISIALISASI WHATSAPP ---
const client = new Client({
    authStrategy: new LocalAuth(),
    // Tambahkan ini agar library menggunakan versi WhatsApp Web yang stabil
    webVersionCache: {
        type: 'remote',
        remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html',
    },
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu'
        ],
    }
});

// Munculkan QR Code di Terminal
client.on('qr', (qr) => {
    console.log('SCAN QR CODE INI DI WHATSAPP KAMU:');
    qrcode.generate(qr, { small: true });
});

// Jika Berhasil Login
client.on('ready', () => {
    console.log('✅ Chatbot RSUD dr. Soedarso siap melayani!');
});

// --- 3. LOGIKA PESAN MASUK ---
client.on('message', async (msg) => {
    if (msg.from.includes('@g.us')) return;

        try {
            console.log("Step 1: Menyiapkan Prompt...");
            const chat = await msg.getChat();
            await chat.sendStateTyping();

            const fullPrompt = `${systemInstruction}\n\nUser berkata: "${msg.body}"`;
            
            console.log("Step 2: Memanggil Gemini API...");
            const result = await model.generateContent(fullPrompt);
            const response = await result.response;
            const resultText = response.text();
            console.log("Step 3: Gemini Menjawab:", resultText);

            // Membersihkan format JSON dari backticks
            const cleanJson = resultText.replace(/```json|```/g, "").trim();
            
            console.log("Step 4: Parsing JSON...");
            const extractedData = JSON.parse(cleanJson);
            console.log("Data Ter-ekstrak:", extractedData);

            await msg.reply(`Kategori: ${extractedData.category}\nMasalah: ${extractedData.summary}`);
            console.log("Step 5: Balasan terkirim! ✅");

        } catch (error) {
            console.error("❌ NYANGKUT DI SINI:", error.message);
            msg.reply("Waduh, otak saya lagi muter-muter. Coba lagi ya!");
        }
});

// Jalankan Mesinnya!
client.initialize();