<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parse Novel to Kindle</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-md">
    <h1 class="text-2xl font-bold text-gray-800 mb-4 text-center">Parse Novel to Kindle</h1>
    <form id="parseForm" class="space-y-4">
        <!-- Email Input -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email"
                   class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Insira seu email">
        </div>

        <!-- Link Input -->
        <div>
            <label for="url" class="block text-sm font-medium text-gray-700">Link do capítulo</label>
            <input type="text" id="url" name="url"
                   class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Insira o link do capítulo">
        </div>

        <!-- Quantidade Input -->
        <div>
            <label for="quantidade" class="block text-sm font-medium text-gray-700">Quantidade de capítulos</label>
            <input type="number" id="quantidade" name="quantidade"
                   class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Insira a quantidade de capítulos" min="1">
        </div>

        <!-- Confirm Button -->
        <div class="text-center">
            <button type="submit" id="submitButton"
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 focus:ring-4 focus:ring-blue-300">
                Confirmar
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Load email from localStorage
        const emailInput = document.getElementById('email');
        const savedEmail = localStorage.getItem('email');
        const inputQuantidade = document.getElementById('quantidade')
        const inputUrl = document.getElementById('url')
        if (savedEmail) {
            emailInput.value = savedEmail;
        }

        // Save email to localStorage on change
        emailInput.addEventListener('input', () => {
            localStorage.setItem('email', emailInput.value);
        });

        // Handle form submission
        const form = document.getElementById('parseForm');
        const submitButton = document.getElementById('submitButton');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Change button state to "Carregando..."
            submitButton.disabled = true;
            submitButton.textContent = "Carregando...";

            const data = {
                email: emailInput.value,
                url: form.url.value,
                quantidade: form.quantidade.value
            };

            try {
                const response = await fetch('/api/parse', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    alert('Dados enviados com sucesso!');

                    inputQuantidade.value = '';
                    inputUrl.value = '';
                    localStorage.removeItem('email');
                } else {
                    const errorData = await response.json();
                    console.error('Erro na resposta:', errorData);
                    alert(`Erro ao enviar os dados: ${errorData.message || 'Erro desconhecido'}`);
                }
            } catch (error) {
                console.error('Erro ao conectar:', error);
                alert('Erro ao conectar ao servidor.');
            } finally {
                // Reset button state
                submitButton.disabled = false;
                submitButton.textContent = "Confirmar";
            }
        });
    });
</script>
</body>
</html>
