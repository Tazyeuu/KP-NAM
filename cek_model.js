require('dotenv').config();
const { GoogleGenAI } = require('@google/genai');

async function listModels() {
    console.log("1. Memulai inisialisasi AI...");
    const client = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });
    
    try {
        console.log("2. Menghubungi server Google (Mohon tunggu)...");
        const result = await client.models.list();
        
        console.log("3. Data berhasil diterima!");
        
        // Cek apakah 'result' punya isi atau tidak
        if (!result || !result.models) {
            console.log("⚠️ Hasil diterima, tapi tidak ada daftar model di dalamnya.");
            console.log("Isi result:", JSON.stringify(result, null, 2));
            return;
        }

        console.log(`\n=== DAFTAR MODEL (${result.models.length} ditemukan) ===`);
        
        result.models.forEach((m, index) => {
            console.log(`${index + 1}. ID: ${m.name}`);
            // Kita filter yang bisa generate konten saja biar gak pusing
            if (m.supportedGenerationMethods.includes("generateContent")) {
                console.log(`   ✨ Support Chat/Content Generation`);
            }
            console.log('-----------------------------------');
        });

    } catch (error) {
        console.error("❌ Terjadi kesalahan saat memanggil API:");
        console.error(`Pesan Error: ${error.message}`);
    }
}

// PASTIKAN BARIS INI ADA DI PALING BAWAH
console.log("--- SCRIPT CEK MODEL DIJALANKAN ---");
listModels();