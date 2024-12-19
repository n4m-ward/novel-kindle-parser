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

        <div class="text-center">
            <button type="submit" id="submitButton"
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 focus:ring-4 focus:ring-blue-300">
                Confirmar
            </button>
        </div>
    </form>
    <div class="text-center mt-2">
        <a href="/log-viewer" target="_blank">
            <button type="submit" id="submitButton"
                    class="w-full bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 focus:ring-4 focus:ring-blue-300">
                Logs
            </button>
        </a>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('parseForm');
        const submitButton = document.getElementById('submitButton');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('email');
            const inputQuantidade = document.getElementById('quantidade')
            const inputUrl = document.getElementById('url')

            submitButton.disabled = true;
            submitButton.textContent = "Carregando...";
            localStorage.setItem('email', emailInput.value);

            const data = {
                email: emailInput.value,
                url: form.url.value,
                quantity: form.quantidade.value
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
                } else {
                    const errorData = await response.json();
                    console.error('Erro na resposta:', errorData);
                    alert(`Erro ao enviar os dados: ${errorData.message || 'Erro desconhecido'}`);
                }
            } catch (error) {
                console.error('Erro ao conectar:', error);
                alert('Erro ao conectar ao servidor.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = "Confirmar";
            }
        });
    });

    window.onload = () => {
        document.getElementById('email').value = localStorage.getItem('email') ?? '';
    }
</script>
</body>
</html>
