const express = require("express");
const cors = require("cors");
const bodyParser = require("body-parser");
const mongoose = require("mongoose");
const path = require("path");

// الاتصال بقاعدة بيانات MongoDB
mongoose.connect("mongodb+srv://oussamabengaoua:C18l7wMgDE6bUOzJ@cluster0.npiaa3g.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0")
  .then(() => console.log("✅ Connected to MongoDB Atlas"))
  .catch(err => console.error("❌ MongoDB Connection Error:", err));


// تهيئة تطبيق Express
const app = express();
app.use(cors());
app.use(bodyParser.json());
app.use(express.static("public"));

// إنشاء مخطط (Schema) ونموذج (Model) التهاني مع إضافة حقل الهاتف (اختياري)
const GreetingSchema = new mongoose.Schema({
    name: String,
    message: String,
    phone: { type: String, default: "" },
    country: String,
    region: String,
    date: { type: Date, default: Date.now }
});
const Greeting = mongoose.model("Greeting", GreetingSchema);

// حفظ تهنئة جديدة (POST /greet)
app.post("/greet", async (req, res) => {
    const { name, message, phone, country, region } = req.body;
    // رقم الهاتف اختياري، أما باقي الحقول فهي مطلوبة
    if (!name || !message || !country || !region) {
        return res.status(400).json({ msg: "يرجى ملء جميع الحقول المطلوبة: الاسم، الرسالة، الدولة والمنطقة!" });
    }
    try {
        const newGreeting = new Greeting({ name, message, phone: phone || "", country, region });
        await newGreeting.save();
        res.json({ msg: `🎉 شكراً ${name}! تم حفظ رسالتك في قاعدة البيانات.` });
    } catch (err) {
        res.status(500).json({ msg: "حدث خطأ أثناء الحفظ.", error: err.message });
        console.error("❌ خطأ أثناء الحفظ:", err);
    }
});

// جلب آخر 3 تهاني فقط (GET /greetings)
app.get("/greetings", async (req, res) => {
    try {
        const greetings = await Greeting.find().sort({ date: -1 }).limit(3);
        res.json(greetings);
    } catch (err) {
        res.status(500).json({ msg: "حدث خطأ أثناء جلب البيانات." });
    }
});

// جلب عدد التهاني (GET /count)
app.get("/count", async (req, res) => {
    try {
        const count = await Greeting.countDocuments();
        res.json({ count });
    } catch (err) {
        res.status(500).json({ msg: "حدث خطأ أثناء حساب عدد التهاني." });
    }
});

// عرض جميع التهاني في جدول HTML مع رقم الهاتف (GET /greet)
app.get("/greet", async (req, res) => {
    try {
        const greetings = await Greeting.find().sort({ date: -1 });
        let html = `
            <!DOCTYPE html>
            <html lang="ar">
            <head>
                <meta charset="UTF-8">
                <title>رسائل التهنئة</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                    th { background-color: #f2f2f2; }
                    h1 { text-align: center; }
                </style>
            </head>
            <body>
                <h1>رسائل التهنئة</h1>
                <table>
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الرسالة</th>
                            <th>الهاتف</th>
                            <th>الدولة</th>
                            <th>الولاية/المنطقة</th>
                            <th>التاريخ والوقت</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        greetings.forEach(greet => {
            const dateStr = new Date(greet.date).toLocaleString("ar");
            html += `<tr>
                        <td>${greet.name}</td>
                        <td>${greet.message}</td>
                        <td>${greet.phone || "—"}</td>
                        <td>${greet.country}</td>
                        <td>${greet.region}</td>
                        <td>${dateStr}</td>
                    </tr>`;
        });
        html += `
                    </tbody>
                </table>
            </body>
            </html>
        `;
        res.send(html);
    } catch (err) {
        res.status(500).send("<p>حدث خطأ أثناء جلب البيانات.</p>");
    }
});

// تقديم الصفحة الرئيسية (GET /)
app.get("/", (req, res) => {
    res.sendFile(path.join(__dirname, "public", "index.html"));
});

// تشغيل السيرفر على المنفذ 3000
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`🚀 Server is running on http://localhost:${PORT}`);
});
