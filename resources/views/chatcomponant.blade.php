<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Chat -->
                <div class="flex flex-col h-80">
                    <div class="flex-grow overflow-y-auto">
                        <div id="chat-container">
                            <!-- Chat Bubble -->
                            <ul class="p-4 space-y-5" id="chat-messages"></ul>
                            <!-- Write & Send button -->
                            <div class="p-4">
                                <label for="hs-trailing-button-add-on" class="sr-only">Label</label>
                                <div class="flex rounded-lg shadow-sm">
                                    @csrf
                                    <input type="text" id="textMsg" name="message"
                                           class="py-3 px-4 block w-full border-gray-200 shadow-sm rounded-s-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none">
                                    <button type="button" id="onClickDataSend"
                                            class="py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-e-md border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                                        Button
                                    </button>
                                </div>
                            </div>
                            <div class="bg-blue-100 p-6">
                                <p>Qui est la :</p>
                                <ul id="userPresent"></ul>
                            </div>
                            <!-- End Chat Bubble -->
                            <script>
                                {{-- When document is loading --}}
                                document.addEventListener('DOMContentLoaded', function () {
                                    // Send function
                                    const sendData = () => {
                                        let textMsg = document.getElementById('textMsg').value;
                                        let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                        let data = {
                                            message: textMsg,
                                            _token: token
                                        };

                                        fetch('{{ url('/send-message') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                // 'X-Socket-ID': window.Echo.socketId(),
                                                'X-CSRF-TOKEN': token
                                            },
                                            body: JSON.stringify(data)
                                        }).then(response => {
                                            console.log(response);
                                            document.getElementById('textMsg').value = '';
                                        }).catch(error => {
                                            console.log(error);
                                        });
                                    }

                                    // Generate messages function
                                    const generateMessages = (messages) => {
                                        let currentUser = @json(Auth::user());

                                        let chatContainer = document.getElementById('chat-messages');
                                        let chatHTML = '';

                                        messages.forEach(message => {
                                            // Si le message n'est pas de l'utilisateur connecté
                                            if (message.user_id !== currentUser.id) {
                                                chatHTML += `
                                                <li class="max-w-lg flex gap-x-2 sm:gap-x-4">
                                                    <div class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                                        <h2 class="font-medium text-gray-800">
                                                            ${message.user.name}
                                                        </h2>
                                                        <div class="space-y-1.5">
                                                            <p class="mb-1.5 text-sm text-gray-800">
                                                                ${message.message}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </li>
                                            `;
                                            } else {
                                                // Si le message est de l'utilisateur connecté
                                                chatHTML += `
                                                <li class="max-w-lg ms-auto flex justify-end gap-x-2 sm:gap-x-4">
                                                    <div class="grow text-end space-y-3">
                                                        <div class="inline-block bg-blue-600 rounded-2xl p-4 shadow-sm">
                                                            <p class="text-sm text-white">
                                                                ${message.user.name}
                                                            </p>
                                                            <p class="mb-1.5 text-sm text-white">
                                                                ${message.message}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </li>
                                            `;
                                            }
                                        });

                                        chatContainer.innerHTML = chatHTML;
                                        document.getElementById('chat-container').parentNode.scrollTo(0, document.getElementById('chat-container').parentNode.scrollHeight);
                                    }

                                    // EDIT : Ajout dans le fichier resources/js/bootstrap.js
                                    // let  laravelEcho = new Echo({
                                    //     broadcaster: "pusher",
                                    //     authEndpoint : 'http://devintrateis.teis.toshibatec.local/soketi_tuto/public/broadcasting/auth',
                                    //     key: env.VITE_PUSHER_APP_KEY,
                                    //     cluster: env.VITE_PUSHER_APP_CLUSTER,
                                    //     wsHost:
                                    //         env.VITE_PUSHER_HOST ??
                                    //         `ws-${env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
                                    //     wsPort: env.VITE_PUSHER_PORT ?? 80,
                                    //     wssPort: env.VITE_PUSHER_PORT ?? 443,
                                    //     forceTLS: false,
                                    //     encrypted: true,
                                    //     disableStats: true,
                                    //     enabledTransports: ["ws"]
                                    // });

                                    let listeningChannels = [];
                                    const startListening = (channelName, eventName) => {
                                        console.log("Subscribe on ", channelName, eventName);
                                        let channel = Echo.channel(channelName);
                                        channel.listen(eventName, (e) => {
                                            console.log("Received event", e);
                                            generateMessages(JSON.parse(e.messages));
                                        });
                                        listeningChannels.push(channelName);
                                    }

                                    const stopListening = (channelName) => {
                                        let channel = Echo.channel(channelName);
                                        channel.stopListening();
                                        let index = listeningChannels.indexOf(channelName);
                                        if (index !== -1) {
                                            listeningChannels.splice(index, 1);
                                        }
                                    }

                                    const isListening = (channelName) => {
                                        return listeningChannels.includes(channelName);
                                    }

                                    let echo = Echo.join('Presencenewmessage');
                                    let channelName = 'messages';
                                    let eventName = 'NewMessageEvent';

                                    if (Echo.connector.pusher.connection.state !== 'connected') {
                                        Echo.connector.pusher.connection.connect();
                                    }

                                    // Connect to websocket function
                                    const connectWS = () => {
                                        let interval = setInterval(() => {
                                            let connectionState = Echo.connector.pusher.connection.state;
                                            console.log("connectionState", connectionState);

                                            if (connectionState === 'connected' && !isListening(channelName)) {
                                                console.log("Connected to WebSocket, NOW ! subscribing to channel");
                                                startListening(channelName, eventName);
                                            } else if (connectionState !== 'connected') {
                                                Echo.connector.pusher.connection.connect();
                                            }

                                            echo.here(updateUserList)
                                                .joining(updateUserList)
                                                .leaving(updateUserList)
                                                .error(console.log)
                                                .subscribe(console.log);
                                        }, 1000);
                                    }

                                    const updateUserList = (users) => {
                                        let userPresent = document.getElementById('userPresent');
                                        let userPresentHTML = '';
                                        users.forEach(user => {
                                            userPresentHTML += `<li>${user.name}</li>`;
                                        });
                                        userPresent.innerHTML = userPresentHTML;
                                    }

                                    // Echo.connector.pusher.connection.bind('disconnected', function () {
                                    //     console.log("disconnected from WebSocket");
                                    //     connectWS();
                                    // });
                                    //
                                    // Echo.connector.pusher.connection.bind('error', function (err) {
                                    //     console.log("error from WebSocket", err);
                                    //     connectWS();
                                    // });
                                    connectWS();
                                    let messages = @json($messages);
                                    generateMessages(messages);
                                    document.getElementById('onClickDataSend').addEventListener('click', sendData);
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
