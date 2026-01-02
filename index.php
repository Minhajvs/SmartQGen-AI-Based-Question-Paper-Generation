<?php
// index.php
include 'connection.php'; // Ensure your database connection

$api_key = 'AIzaSyAGTkF8vWxZlb8d7wK4ZvDS-tDy75oQBdk'; // Replace with your API key

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['syllabus']) && $_FILES['syllabus']['error'] == 0) {
    $difficulty = $_POST['difficulty']; 
    $file_path = $_FILES['syllabus']['tmp_name'];
    $file_size = $_FILES['syllabus']['size'];

    // Validate file type
    $file_type = mime_content_type($file_path);
    $allowed_types = ['image/png', 'image/jpeg', 'application/pdf'];
    if (!in_array($file_type, $allowed_types)) {
        die("Error: Unsupported file format. Use PNG, JPEG, or PDF.");
    }

    // Convert file to base64
    $file_data = base64_encode(file_get_contents($file_path));

    // AI Request
    $api_url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=$api_key";
    $data = json_encode([
        "contents" => [
            [
                "parts" => [
                    ["text" => "Extract syllabus details and generate:\n
                        - 10 three-mark questions (together)\n
                        - Two seven-mark questions per module (together)\n
                        - Difficulty: '$difficulty'"],
                    ["inlineData" => [
                        "mimeType" => $file_type,
                        "data" => $file_data
                    ]]
                ]
            ]
        ]
    ]);

    // cURL Request
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        exit;
    }
    curl_close($ch);

    $response_data = json_decode($response, true);
    $questions = $response_data['candidates'][0]['content']['parts'][0]['text'] ?? "Error processing the request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Syllabus</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> <!-- jsPDF Library -->
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        .container { padding: 20px; border-radius: 12px; max-width: 700px; margin: auto; }
        button { padding: 10px; cursor: pointer; }
    </style>
    <script>
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
    </script>
</head>

<body>
    <div class="container">
        <h2>Upload Syllabus for AI Question Generation</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="syllabus" required>
            <select name="difficulty" required>
                <option value="Easy">Easy</option>
                <option value="Medium">Medium</option>
                <option value="Hard">Hard</option>
            </select>
            <button type="submit">Generate Questions</button>
        </form>

        <?php if (isset($questions)): ?>
            <h3>Generated Questions:</h3>
            <pre id="generated-questions"><?php echo htmlspecialchars($questions); ?></pre>
            <button onclick="copyToClipboard()">Copy</button>
            <button onclick="downloadPDF()">Download as PDF</button>
        <?php endif; ?>
    </div>
</body>
</html>