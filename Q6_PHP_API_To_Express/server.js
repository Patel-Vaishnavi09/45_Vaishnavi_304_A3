
const express = require("express");

const app = express();
const PORT = 3001;

app.get("/students", async (req, res) => {
    try {
        const response = await fetch(
            "http://localhost/PHP_Practical_Assignment/Q6_PHP_API_To_Express/api.php"
        );

        if (!response.ok) {
            return res.status(response.status).json({
                error: "PHP API request failed"
            });
        }

        const data = await response.json();
        res.json({
            message: "Data received from PHP API",
            students: data
        });
    } catch (error) {
        res.status(500).json({
            error: "Unable to connect to PHP API"
        });
    }
});

app.listen(PORT, () => {
    console.log(`Express server running at http://localhost:${PORT}`);
});
