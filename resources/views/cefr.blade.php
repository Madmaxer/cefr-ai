<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CEFR Language Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .question, .result {
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        button.recording {
            background-color: #dc3545;
        }
        .result {
            display: none;
            padding: 15px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
<h1>CEFR Language Test ({{ strtoupper($language) }})</h1>

<div id="test-container">
    <div class="question">
        <p id="current-question">Please wait, starting the test...</p>
        <button id="start-test">Start Test</button>
        <button id="record-response" disabled>🎤 Speak</button>
    </div>
</div>

<div id="result-container" class="result">
    <h2>Your CEFR Level</h2>
    <p><strong>Level:</strong> <span id="cefr-level"></span></p>
    <p><strong>Description:</strong> <span id="cefr-description"></span></p>
</div>

<script>
    const userId = '{{ auth()->user() ? auth()->user()->id : "test-user" }}';
    const language = '{{ $language }}';
    const speechApiCode = '{{ $speechApiCode }}'; // np. "en-US"
    const apiEndpoint = `/api/language-test/${userId}`;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const questionElement = document.getElementById('current-question');
    const startButton = document.getElementById('start-test');
    const recordButton = document.getElementById('record-response');
    const resultContainer = document.getElementById('result-container');
    const cefrLevelElement = document.getElementById('cefr-level');
    const cefrDescriptionElement = document.getElementById('cefr-description');

    // Inicjalizacja SpeechSynthesis (Text-to-Speech)
    const synth = window.speechSynthesis;
    let voices = [];

    function loadVoices() {
        voices = synth.getVoices();
        // Wybierz głos pasujący do języka
        const voice = voices.find(v => v.lang === speechApiCode) || voices.find(v => v.lang.startsWith(language));
        return voice || voices[0]; // Domyślny głos, jeśli brak pasującego
    }

    function speak(text) {
        const utterance = new SpeechSynthesisUtterance(text);
        const voice = loadVoices();
        utterance.voice = voice;
        utterance.lang = speechApiCode;
        synth.speak(utterance);
        return utterance;
    }

    // Inicjalizacja SpeechRecognition (Speech-to-Text)
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition;
    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.lang = speechApiCode;
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            handleResponse(transcript);
            recordButton.textContent = '🎤 Speak';
            recordButton.classList.remove('recording');
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            alert('Microphone error: ' + event.error);
            recordButton.textContent = '🎤 Speak';
            recordButton.classList.remove('recording');
            startButton.disabled = false;
        };

        recognition.onend = () => {
            recordButton.textContent = '🎤 Speak';
            recordButton.classList.remove('recording');
        };
    } else {
        alert('Speech recognition not supported in this browser.');
        recordButton.disabled = true;
        startButton.disabled = true;
    }

    // Funkcja wysyłająca odpowiedź do API
    async function sendResponse(response) {
        try {
            const res = await fetch(apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    response: response,
                    language: language,
                }),
            });

            if (!res.ok) throw new Error('API request failed');
            return await res.json();
        } catch (error) {
            console.error('Error:', error);
            alert('Something went wrong. Please try again.');
        }
    }

    // Obsługa odpowiedzi użytkownika
    async function handleResponse(response) {
        const data = await sendResponse(response);
        if (data) {
            if (data.finished) {
                resultContainer.style.display = 'block';
                cefrLevelElement.textContent = data.level;
                cefrDescriptionElement.textContent = data.description;
                document.getElementById('test-container').style.display = 'none';
                speak(`Your CEFR level is ${data.level}. Here’s the description: ${data.description}`);
            } else if (data.next_question) {
                const cleanQuestion = data.next_question.replace(/^\[(Question|More Details)\]\s*/, '');
                questionElement.textContent = cleanQuestion;
                const utterance = speak(cleanQuestion);
                utterance.onend = () => {
                    recordButton.disabled = false;
                };
            }
        }
    }

    // Start testu
    startButton.addEventListener('click', async () => {
        startButton.disabled = true;
        recordButton.disabled = true;

        const welcomeMessage = "Welcome to the CEFR Language Test. I will ask you questions to assess your language level. Please speak clearly when answering.";
        questionElement.textContent = welcomeMessage;
        const welcomeUtterance = speak(welcomeMessage);

        welcomeUtterance.onend = async () => {
            const data = await sendResponse('');
            if (data && data.next_question) {
                const cleanQuestion = data.next_question.replace(/^\[(Question|More Details)\]\s*/, '');
                questionElement.textContent = cleanQuestion;
                const questionUtterance = speak(cleanQuestion);
                questionUtterance.onend = () => {
                    recordButton.disabled = false;
                };
            }
        };
    });

    // Obsługa nagrywania
    recordButton.addEventListener('click', () => {
        if (!recognition || recordButton.classList.contains('recording')) return;

        recognition.start();
        recordButton.textContent = '🎤 Stop';
        recordButton.classList.add('recording');
    });
</script>
</body>
</html>
