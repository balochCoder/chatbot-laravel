<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <title>Codex</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Alegreya+Sans:wght@100;300;400;500;700;800;900&display=swap");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Alegreya Sans", sans-serif;
        }

        body {
            background: #343541;
        }

        #app {
            width: 100vw;
            height: 100vh;
            background: #343541;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        #chat_container {
            flex: 1;
            width: 100%;
            height: 100%;
            overflow-y: scroll;

            display: flex;
            flex-direction: column;
            gap: 10px;

            -ms-overflow-style: none;
            scrollbar-width: none;

            padding-bottom: 20px;
            scroll-behavior: smooth;
        }

        /* hides scrollbar */
        #chat_container::-webkit-scrollbar {
            display: none;
        }

        .wrapper {
            width: 100%;
            padding: 15px;
        }

        .ai {
            background: #40414F;
        }

        .chat {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;

            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 10px;
        }

        .profile {
            width: 36px;
            height: 36px;
            border-radius: 5px;

            background: #5436DA;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .ai .profile {
            background: #10a37f;
        }

        .profile img {
            width: 60%;
            height: 60%;
            object-fit: contain;
        }

        .message {
            flex: 1;

            color: #dcdcdc;
            font-size: 20px;

            max-width: 100%;
            overflow-x: scroll;

            /*
             * white space refers to any spaces, tabs, or newline characters that are used to format the CSS code
             * specifies how white space within an element should be handled. It is similar to the "pre" value, which tells the browser to treat all white space as significant and to preserve it exactly as it appears in the source code.
             * The pre-wrap value allows the browser to wrap long lines of text onto multiple lines if necessary.
             * The default value for the white-space property in CSS is "normal". This tells the browser to collapse multiple white space characters into a single space, and to wrap text onto multiple lines as needed to fit within its container.
            */
            white-space: pre-wrap;

            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* hides scrollbar */
        .message::-webkit-scrollbar {
            display: none;
        }

        form {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 10px;
            background: #40414F;

            display: flex;
            flex-direction: row;
            gap: 10px;
        }

        textarea {
            width: 100%;

            color: #fff;
            font-size: 18px;

            padding: 10px;
            background: transparent;
            border-radius: 5px;
            border: none;
            outline: none;
        }

        button {
            outline: 0;
            border: 0;
            cursor: pointer;
            background: transparent;
        }

        form img {
            width: 30px;
            height: 30px;
        }
    </style>
</head>
<body>
<div id="app">
    <div id="chat_container">

    </div>
    <form method="POST">
        @csrf
        <textarea name="prompt" id="" cols="1" rows="1" placeholder="Ask Codex..."></textarea>
        <button type="submit"><img src="{{asset('assets/send.svg')}}"></button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>


<script>
    const form = document.querySelector('form');
    const chatContainer = document.querySelector('#chat_container');

    let loadInterval;

    function loader(element) {
        element.textContent = '';

        loadInterval = setInterval(() => {
            element.textContent += '.';

            if (element.textContent === '....') {
                element.textContent = '';
            }
        }, 300);
    }

    function typeText(element, text) {
        let index = 0;
        let interval = setInterval(() => {
            if (index < text.length) {
                element.innerHTML += text.charAt(index);
                index++;
            } else {
                clearInterval(interval);
            }
        }, 20);
    }

    function generateUniqueId() {
        const timestamp = Date.now();
        const randomNumber = Math.random();
        const hexadecimalString = randomNumber.toString(16);
        return `id-${timestamp}-${hexadecimalString}`;
    }

    function chatStripe(isAi, value, uniqueId) {
        return (
            `
        <div class="wrapper ${isAi && 'ai'}">
            <div class="chat">
                <div class="profile">
                    <img src="${isAi ? '/assets/bot.svg' : '/assets/user.svg'}" alt="${isAi ? 'bot' : 'user'}"/>
                </div>
                <div class="message" id="${uniqueId}">${value}</div>
            </div>
        </div>
        `
        )
    }

    const handleSubmit = (e) => {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();

        const data = new FormData(form);
//     Users chat stripe
        chatContainer.innerHTML += chatStripe(false, data.get('prompt'));

        form.reset();

//     Bot chat stripe
        const uniqueId = generateUniqueId();
        chatContainer.innerHTML += chatStripe(true, " ", uniqueId);
        chatContainer.scrollTop = chatContainer.scrollHeight;

        const messageDiv = document.getElementById(uniqueId);
        loader(messageDiv);

        $.ajax({
            type: 'POST',
            url: '{{route('check')}}',
            data: {
                prompt: data.get('prompt'),
            },
            dataType: 'json',
            success: function (data) {
                clearInterval(loadInterval);
                messageDiv.innerHTML = '';
                const parsedData = data.bot.trim();
                typeText(messageDiv, parsedData)


            },
            error: function (data) {
                clearInterval(loadInterval);
                messageDiv.innerHTML = '';
                const err = data.text();
                messageDiv.innerHTML = "Something went wrong";
                alert(err);
            }
        })
    }

    form.addEventListener('submit', handleSubmit);
    form.addEventListener('keyup', (e) => {
        if (e.keyCode === 13) {
            handleSubmit(e);
        }
    })
</script>
</body>
</html>
