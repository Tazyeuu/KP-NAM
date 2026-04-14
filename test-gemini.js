require('dotenv').config();
const { GoogleGenAI } = require('@google/genai');

async function testGemini() {
    console.log("--- MENCOBA MODEL DARI DAFTAR CURL ---");
    try {
        // BARIS INI YANG TADI KETINGGALAN, BAL:
        const client = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });

        const result = await client.models.generateContent({
            model: 'gemini-2.5-flash', 
            contents: [{ role: 'user', parts: [{ text: 'Halo, ini tes terakhir. Jawab dengan kata: BERHASIL' }] }]
        });

        // Cara ambil teks di SDK @google/genai versi 2026
        if (result && result.candidates && result.candidates[0]) {
            const responseText = result.candidates[0].content.parts[0].text;
            console.log("RESPON GEMINI:", responseText);
            console.log("--- KONEKSI BERHASIL! ✅ ---");
        } else {
            console.log("Data diterima tapi strukturnya beda. Cek ini:", JSON.stringify(result, null, 2));
        }
    } catch (error) {
        console.error("--- KONEKSI GAGAL! ❌ ---");
        console.log("Detail Error:", error.message);
    }
}

testGemini();