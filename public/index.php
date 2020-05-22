<!DOCTYPE html>

<html>
<head>
<title>JS Démineur</title>
<link rel="stylesheet" href="asset/css/style.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css"/>

</head>
<body>
    


<script>

class Demineur
{
    width = 8;
    height = 8;
    mineNumber = 9;

    matrix = [];

    markedCells = [];
    minedCells = {};

    end = false;
    started = false;

    interval = null;
    chronometer = 0;


    vitoryImageURL = 'https://i.kym-cdn.com/photos/images/facebook/000/068/108/HM446UQ6GA5GROAJXNHREYMR2VO7PKRA.jpeg';
    loseImageURL = 'https://media1.tenor.com/images/bb654fabb96e49b1402555bfeaf095ef/tenor.gif?itemid=8596700';

    constructor(width = 8, height = 8, mineNumber = 9) {
        this.width = width;
        this.height = height;
        this.mineNumber = mineNumber;


    }

    generateMatrix() {
        this.matrix = [];
        this.minedCells = [];
        this.markedCells = {};

        let mineCount = 0;

        for(let x = 0 ; x < this.width; x++) {
            for(let y = 0 ; y < this.height ; y++) {
                if(typeof(this.matrix[x]) === 'undefined') {
                    this.matrix[x] = [];
                }
                this.matrix[x][y] = 0;
               
            }
        }

        for(let i = 0 ; i < this.mineNumber ; i++) {
            let mineGenerated = false;
            do {
                mineGenerated = this.generateMine()
            } while(!mineGenerated)
        }

        for(let x = 0 ; x < this.width; x++) {
            for(let y = 0 ; y < this.height ; y++) {
                this.matrix[x][y] = this.computeContent(x, y);
            }
        }


        console.log(this);

     }

    generateMine()
    {
        let x = Math.trunc(Math.random() * this.width);
        let y = Math.trunc(Math.random() * this.height);
        if(this.matrix[x][y] !== false) {
            this.matrix[x][y] = false;

            this.minedCells.push({
                x:x,
                y:y
            });

            return true;
        }
        
        return false;
    }

    computeContent(x, y) {
        if(this.matrix[x][y] !== 0 ) {
            return this.matrix[x][y];
        }

        let count = 0;

        if(y > 0) {
            if(x>0 && this.cell(x-1,y-1) === false) {count -= 1;}
            if(this.cell(x,y-1) === false) {count -= 1;}
            if(x < this.width-1 && this.cell(x+1,y-1) === false) {count -= 1}
        }

        if(x>0 && this.cell(x-1,y) === false) {count -= 1;}
        if(x < this.width-1 && this.cell(x+1,y) === false) {count -= 1}

        if(y < this.height-1) {
            if(x>0 && this.cell(x-1,y+1) === false) {count -= 1;}
            if(this.cell(x,y+1) === false) {count -= 1;}
            if(x < this.width-1 && this.cell(x+1,y+1) === false) {count -= 1}
        }

        return count;
    }
          



    contextOnCell(evt) {

        if(this.end) {
            return false;
        }

        if(!this.start) {
            this.start();
        }

        evt.preventDefault();
        let td = evt.currentTarget;
        let x  = parseInt(td.dataset.x);
        let y  = parseInt(td.dataset.y);
        if(td.classList.contains('revealed')) {
            return;
        }

        if(td.classList.contains('marked')) {
            td.classList.remove('marked');
            td.classList.add('reserved');
            delete this.markedCells[x+'-'+y];
        }
        else if(td.classList.contains('reserved')) {
            td.classList.remove('reserved');
            delete this.markedCells[x+'-'+y];
        }
        else if(!td.classList.contains('marked')) {
            td.classList.add('marked');
            this.markedCells[x+'-'+y] = {x:x, y:y};
        }

        this.mineCounter.innerHTML = this.mineNumber - this.element.querySelectorAll('.marked').length;

        if(this.checkVictory()) {
            this.win();
        }
    }


    clicOnCell(evt) {
        if(this.end) {
            return false;
        }
        if(!this.started) {
            this.start();
        }

        let td = evt.currentTarget;
        let x  = parseInt(td.dataset.x);
        let y  = parseInt(td.dataset.y);
        this.reveal(x,y);

        if(this.checkVictory()) {
            this.win();
        }
    }



    render(container)
     {
        this.container = container;
        if(!this.element) {
            this.element = document.createElement('div');

            this.element.appendChild(
                this.getLooseScreen()
            );

            this.element.appendChild(
                this.getVictoryScreen()
            );

            this.element.className = 'demineur';
            this.renderHeader();

            this.field = document.createElement('table');


            this.element.appendChild(this.field);
            this.container.appendChild(this.element);

        }

        this.field.innerHTML = '';
        
        
        for(let y = 0 ; y < this.height; y++) {
            let tr= document.createElement('tr');
            for(let x = 0 ; x < this.width; x++) {
                let td = document.createElement('td');
                td.dataset.x = x;
                td.dataset.y = y;
                tr.appendChild(td);

                td.addEventListener('click', (event) => {this.clicOnCell(event)})
                td.addEventListener('contextmenu', (event) => {this.contextOnCell(event)})

            }
            this.field.appendChild(tr);
        }
    }

    renderHeader()
    {
        this.header = document.createElement('div');
        this.header.classList.add('header');

        this.mineCounter = document.createElement('div');
        this.mineCounter.classList.add('mine-counter');
        this.mineCounter.innerHTML = this.mineNumber;
        this.header.appendChild(this.mineCounter);

        this.filler = document.createElement('div');
        this.filler.innerHTML = '&nbsp;';
        this.filler.classList.add('filler');
        this.header.appendChild(this.filler);

        this.timer = document.createElement('div');
        this.timer.innerHTML = 0;
        this.timer.classList.add('timer');
        this.header.appendChild(this.timer);

        this.element.appendChild(this.header);
    }



    reveal(x, y, auto = false) {
        let td = this.td(x,y);
        let value  = this.cell(x,y);


        if(td.classList.contains('reserved') || td.classList.contains('marked') ) {
            return false;
        }


        if(td.classList.contains('revealed')) {
            return;
        }

        td.classList.add('revealed')
               
        if(value === false && !auto) {
            td.classList.add('mine')
            td.classList.add('fail')
            this.lose();
        }
        else if(value !== 0) {
            td.textContent = Math.abs(value);
        }
        else {
            td.classList.add('void');

            if(y > 0) {
                if(x>0) this.reveal(x-1, y-1, true);
                this.reveal(x, y-1, true)
                if(x < this.width-1) this.reveal(x+1, y-1, true);
            }
            if(x>0) this.reveal(x-1, y, true);
            if(x < this.width-1) this.reveal(x+1, y, true);

            if(y < this.height-1) {
                if(x>0) this.reveal(x-1, y+1);
                this.reveal(x, y+1, true)
                if(x < this.width-1) this.reveal(x+1, y+1, true);
            }

        }
    }


    checkVictory() {
        let founded = 0;
        for(let index in this.markedCells) {
            let marked = this.markedCells[index];

            if(this.cell(marked.x, marked.y) === false) {
                founded++;
            }
        }
        if(founded === this.mineNumber) {
            return true;
        }

        return false;
    }




    lose() {
        this.loseScreen.style.display = 'block';
        this.stop();
    }

    win() {
        this.victoryScreen.style.display = 'block';
        this.stop();
    }

    getLooseScreen() {
        this.loseScreen = document.createElement('div');
        this.loseScreen.classList.add('lose-screen');
        this.loseScreen.innerHTML = '<img src="' + this.loseImageURL + '"/><hr/>';
        this.loseScreen.appendChild(this.getRestartButton());
        
        return this.loseScreen;
    }

    getVictoryScreen() {
        this.victoryScreen = document.createElement('div');
        this.victoryScreen.classList.add('victory-screen');
        this.victoryScreen.innerHTML = '<img src="' + this.vitoryImageURL + '"/><hr/>';
        this.victoryScreen.appendChild(this.getRestartButton());
        return this.victoryScreen;
    }

    getRestartButton() {
        let button = document.createElement('button');
        button.classList.add('add')
        button.innerHTML = 'Rejouer';
        button.addEventListener('click', (evt) => {this.reset()});
        return button;
    }

    reset() {
        this.victoryScreen.style.display = 'none';
        this.loseScreen.style.display = 'none';

        this.end = false;
        this.started = false;
        this.chronometer = 0;

        this.timer.innerHTML = 0;
        this.mineCounter.innerHTML = this.mineNumber;

        this.generateMatrix();
        this.render(this.container);
    }
    


    stop() {
        this.end = true;
        this.end = false;
        clearInterval(this.interval);
        for(let y = 0 ; y < this.height; y++) {
            for(let x = 0 ; x < this.width; x++) {
                let cell = this.cell(x,y);
                if(cell === false) {
                    this.td(x,y).classList.add('mine');
                }
                else {
                    this.td(x,y).classList.add('revealed');
                    //this.td(x,y).textContent = this.cell(x,y);
                }
            }
        }
    }


    start() {
        this.started = true;
        this.end = false;
        this.chronometer = 0;
        this.interval = setInterval(() => {this.tick()}, 1000);
    }

    tick() {
        this.chronometer++;
        this.timer.innerHTML = this.chronometer;
    }

    cell(x,y) {
        return this.matrix[x][y];
    }

    td(x,y) {
        return this.element.querySelector('td[data-x="' + x + '"][data-y="' + y + '"]');
    }
}



const demineur = new Demineur();
demineur.generateMatrix();
demineur.render(document.body);

//demineur.stop();

</script>

</body>

</html>