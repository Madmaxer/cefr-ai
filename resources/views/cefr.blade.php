<!DOCTYPE html>
<html>
<head>
    <title>CEFR Test - {{ ucfirst($language) }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
<h1>Test Language: {{ ucfirst($language) }}</h1>
<button id="startButton">Start</button>
<button id="stopButton">Stop</button>
<div id="output"></div>
<div id="botBubble" class="bot-bubble">Bot</div>

<script type="module">
    const startButton = document.getElementById('startButton');
    const stopButton = document.getElementById('stopButton');
    const output = document.getElementById('output');
    const botBubble = document.getElementById('botBubble');
    const language = "{{ $speechApiCode }}";
    const testLanguage = "{{ $language }}";
    let recognition;
    let isSpeaking = false;
    let isRecognizing = false;
    let lastBotQuestion = '';

    if (!startButton || !stopButton || !output || !botBubble) {
        console.error('One or more elements not found.');
    } else {
        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = false;
            recognition.lang = language;
            recognition.maxAlternatives = 1;

            recognition.onresult = (event) => {
                if (isSpeaking) return; // Ignoruj wyniki, jeśli bot mówi

                const transcript = event.results[event.results.length - 1][0].transcript.trim();
                if (transcript.length < 3 || transcript === lastBotQuestion) return;

                output.textContent = `You said: "${transcript}"`;
                console.log('Recognized:', transcript);

                fetch(`/api/language-test/{{ auth()->user()->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        response: transcript,
                        language: testLanguage
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.next_question) {
                            lastBotQuestion = data.next_question;
                            speak(data.next_question);
                        } else if (data.finished) {
                            output.textContent = `Test finished. Level: ${data.level} - ${data.description}`;
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            };

            recognition.onstart = () => {
                isRecognizing = true;
                output.textContent = 'Listening... Please speak now.';
                console.log('Recognition started');
            };

            recognition.onend = () => {
                isRecognizing = false;
                if (!isSpeaking) {
                    setTimeout(() => {
                        if (!isRecognizing && !isSpeaking) {
                            console.log('Restarting recognition...');
                            recognition.start();
                        }
                    }, 500);
                }
            };

            recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                isRecognizing = false;
                if (event.error === 'no-speech') {
                    output.textContent = 'No speech detected, please try again.';
                    setTimeout(() => {
                        if (!isRecognizing && !isSpeaking) recognition.start();
                    }, 1000);
                } else if (event.error === 'audio-capture') {
                    output.textContent = 'Microphone error: Please check your audio input.';
                } else {
                    output.textContent = `Error: ${event.error}`;
                }
            };
        } else {
            console.error('Speech recognition not supported.');
            output.textContent = 'Speech recognition is not supported in your browser.';
        }

        function speak(text) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = language;

            isSpeaking = true;
            if (isRecognizing) {
                recognition.stop(); // Zatrzymaj nasłuchiwanie, gdy bot zaczyna mówić
                console.log('Recognition stopped for bot speech');
            }

            utterance.onstart = () => {
                botBubble.classList.add('speaking');
                botBubble.textContent = text;
                botBubble.style.opacity = '1';
                output.textContent = `Bot: ${text}`;
            };

            utterance.onend = () => {
                isSpeaking = false;
                botBubble.classList.remove('speaking');
                botBubble.textContent = 'Bot';
                botBubble.style.opacity = '0.5';
                setTimeout(() => {
                    if (!isRecognizing && !isSpeaking) {
                        console.log('Resuming recognition after bot speech');
                        recognition.start();
                    }
                }, 500);
            };

            window.speechSynthesis.speak(utterance);
        }

        startButton.addEventListener('click', () => {
            if (recognition && !isSpeaking && !isRecognizing) {
                console.log('Starting recognition...');
                recognition.start();
                // Wywołaj pierwsze pytanie po starcie
                fetch(`/api/language-test/{{ auth()->user()->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        response: '',
                        language: testLanguage
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.next_question) {
                            lastBotQuestion = data.next_question;
                            speak(data.next_question);
                        }
                    });
            }
        });

        stopButton.addEventListener('click', () => {
            if (recognition && isRecognizing) {
                recognition.stop();
            }
        });
    }
</script>

<style>
    .bot-bubble {
        width: 60px;
        height: 60px;
        background-color: #4CAF50;
        border-radius: 50%;
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        text-align: center;
        padding: 10px;
        transition: transform 0.2s ease-in-out;
        visibility: visible;
        opacity: 0.5;
    }

    .bot-bubble.speaking {
        opacity: 1;
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
</style>
</body>
</html>
