document.addEventListener("DOMContentLoaded", function () {
    loadGreetings();
    loadMessageCount();
});

// دالة إرسال التهنئة
async function sendGreeting() {
    const name = document.getElementById("name").value.trim();
    const message = document.getElementById("message").value.trim();
    const country = document.getElementById("country").value.trim();
    const region = document.getElementById("region").value.trim();
    const phone = document.getElementById("phone").value.trim(); // رقم الهاتف اختياري

    if (!name || !message || !country || !region) {
        alert("❌ يرجى ملء جميع الحقول المطلوبة: الاسم، الرسالة، الدولة والمنطقة!");
        return;
    }

    try {
        const response = await fetch("/greet", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ name, message, country, region, phone })
        });
        const data = await response.json();
        document.getElementById("responseMsg").innerText = data.msg;
        document.getElementById("greetForm").reset();
        loadGreetings();
        loadMessageCount();
    } catch (error) {
        alert("❌ حدث خطأ أثناء الإرسال.");
        console.error("❌ Error:", error);
    }
}

// دالة تحميل التهاني (جلب جميع الرسائل)
async function loadGreetings() {
    try {
        const response = await fetch("/greetings");
        const greetings = await response.json();
        const container = document.getElementById("greetingsContainer");
        container.innerHTML = "";
        if (greetings.length === 0) {
            container.innerHTML = "<p>لا توجد تهاني بعد، كن أول من يرسل تهنئة! 🎉</p>";
        } else {
            greetings.forEach(greet => {
                container.innerHTML += `<div class="message-box">
                    <strong>${greet.name}</strong>: ${greet.message} <br>
                    <small>الدولة: ${greet.country} - المنطقة: ${greet.region}</small><br>
                    <small>${new Date(greet.date).toLocaleString("ar")}</small>
                </div><hr>`;
            });
        }
    } catch (error) {
        console.error("❌ Error loading greetings:", error);
    }
}

// دالة تحميل عدد التهاني
async function loadMessageCount() {
    try {
        const response = await fetch("/count");
        const data = await response.json();
        document.getElementById("messageCount").textContent = `📢 عدد التهاني المرسلة: ${data.count}`;
    } catch (error) {
        console.error("❌ Error loading message count:", error);
    }
}
