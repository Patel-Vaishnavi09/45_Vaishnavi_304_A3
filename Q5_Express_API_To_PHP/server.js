
const express = require("express");

const app = express();
const PORT = 3000;

app.get("/api/students", (req, res) => {
    res.json([
        { id: 1, name: "Rahul Patel", course: "BCA" },
        { id: 2, name: "Priya Shah", course: "MSc ICT" },
        { id: 3, name: "Amit Joshi", course: "BSc IT" }
    ]);
});

app.listen(PORT, () => {
    console.log(`Express API running at http://localhost:${PORT}`);
});
