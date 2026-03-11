require('dotenv').config();
const { GoogleGenerativeAI } = require("@google/generative-ai");

async function testGemini() {
    console.log("--- MENCOBA HUBUNGI GEMINI ---");
    console.log("API KEY:", process.env.GEMINI_API_KEY ? "Ditemukan ✅" : "Kosong ❌");

    try {
        const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
        const model = genAI.getGenerativeModel({ model: "gemini-2.0-flash" });

        const result = await model.generateContent("Halo Gemini, apakah kamu sudah terhubung?");
        const response = await result.response;
        console.log("RESPON GEMINI:", response.text());
        console.log("--- KONEKSI BERHASIL! ✅ ---");
    } catch (error) {
        console.error("--- KONEKSI GAGAL! ❌ ---");
        console.error("Pesan Error:", error.message);
    }
}

testGemini();