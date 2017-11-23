var showDialog = false;

window.onload = function () {
    var new_game_button = document.getElementById('new-game');
    new_game_button.addEventListener('click', function () {
        Ajax({
            method: 'DELETE',
            action: '/api/game',
            data: {},
            success: function () {
                Game();
            }
        });
    });

    var save_game_button = document.getElementById('save-game');
    save_game_button.addEventListener('click', function (e) {
        e.preventDefault();

        SaveGame();
    });

    var best_result_button = document.getElementById('best-results');
    best_result_button.addEventListener('click', function (e) {
        e.preventDefault();

        GetWinners();
    });

    Game();
};

function Game() {
    Ajax({
        method: 'GET',
        action: '/api/game',
        success: function (data) {
            if (data.state === true && showDialog) {
                showWinDialog();
            }
            drawSquare(data);
        }
    });
}

function SaveGame() {
    Ajax({
        method: 'POST',
        action: '/api/game/save',
        success: function () {
            alert('Игра сохранена')
        }
    });
}

function showWinDialog()
{
    Ajax({
        method: 'GET',
        action: '/api/game/state',
        success: function (data) {
            var message = 'Количество ходов:' + data.clicks + ' ';

            if (data.best_result) {
                message += 'Ваш лучший результат:' + data.best_result;
            }

            document.getElementById('winner-result-text').innerText = message;

            var input = document.getElementById('winner-name');
            input.value = data.name;

            $("#form-win-modal").modal(); // костыль, но все же :-)

            var save_result_button = document.getElementById('winner-save-result');
            save_result_button.addEventListener('click', function (e) {
                e.preventDefault();

                var name = document.getElementById('winner-name');
                Ajax({
                    method: 'POST',
                    action: '/api/game/winners',
                    data: {
                        name: name.value
                    },
                    success: function () {
                        $("#form-win-modal").modal('toggle');
                    }
                });
            });
        }
    });
}

function drawSquare(data) {
    showDialog = true;

    var square = document.getElementById('square');
    if (square) {
        square.innerHTML = "";
        var items = data.square || [];

        var clicks = data.clicks || 0;
        document.getElementById('clicks-count').innerText = clicks;

        if (square) {
            var i;
            var j;

            for (i = 0; i < 5; ++i) {
                for (j = 0; j < 5; ++j) {

                    var item = document.createElement("div");
                    item.className = 'square-item';
                    if (items[i][j] === 1) {
                        item.className += ' on';
                    }

                    item.dataset.row = i;
                    item.dataset.col = j;

                    if (items[i][j] === 0) {
                        item.addEventListener("click", function (e) {
                            Ajax({
                                method: 'POST',
                                action: '/api/game',
                                data: {
                                    row: this.dataset.row,
                                    col: this.dataset.col
                                },
                                success: function (data) {
                                    Game();


                                }
                            });
                        }, false);
                    }

                    square.appendChild(item);
                }
            }
        }
    }
}

function Ajax(options) {
    var prepare = function (object) {
        var encodedString = '';
        for (var prop in object) {
            if (object.hasOwnProperty(prop)) {
                if (encodedString.length > 0) {
                    encodedString += '&';
                }
                encodedString += encodeURI(prop + '=' + object[prop]);
            }
        }
        return encodedString;
    };

    var xhr = new XMLHttpRequest();
    xhr.open(options.method, options.action);
    //xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function () {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);

            options.success(data);
        }
    };

    xhr.send(prepare(options.data));
}

function GetWinners() {
    Ajax({
        method: 'GET',
        action: '/api/game/winners',
        success: function (data) {
            var table = document.getElementById('best-results-table');
            table.innerHTML = '<thead><tr><th scope="col">#</th><th scope="col">Имя</th><th scope="col">Кол-во ходов</th></tr></thead>';

            var items = data.data;
            if (items) {
                for (var index = 0; index < items.length; ++index) {

                    var tr_item = document.createElement('tr');
                    for (key in items[index]) {
                        var td_item = document.createElement('td');

                        td_item.innerHTML = items[index][key];
                        tr_item.appendChild(td_item);
                    }

                    table.appendChild(tr_item);
                }
            }
        }
    });
}
