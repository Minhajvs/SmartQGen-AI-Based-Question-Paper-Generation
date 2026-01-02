// script.js
function copyToClipboard() {
    let text = document.getElementById("generated-questions").innerText;
    navigator.clipboard.writeText(text).then(() => alert("Copied to clipboard!"));
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF();

    doc.setFont("helvetica", "bold");
    doc.text("Generated Question Paper", 10, 10);
    doc.setFont("helvetica", "normal");

    let questions = document.getElementById("generated-questions").innerText;
    let margin = 20;
    let y = 20;

    questions.split("\n").forEach((line) => {
        doc.text(line, margin, y);
        y += 8;
    });

    doc.save("Question_Paper.pdf"); // Downloads the PDF
}