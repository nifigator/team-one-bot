const LIM = 'DELIMITER';
//const METPH = 4;

var express = require('express');
var bodyParser = require('body-parser');
var fs = require('fs');
var os = require("os"); 
var natural = require('natural');
var transliteration = require('transliteration');

var app = express();

app.use(bodyParser.json());

//дописывает данные в файл для обучения нужного класса
app.post('/add/:cls', function(req, res){
	console.log('Добавление данных для обучения класса ' + req.params.cls);
	console.log(req.body);
	
	var mkdirp = require('mkdirp');
	
	mkdirp("./data/" + req.params.cls + "/", function(err) { 
		if (!err) {
			fs.open("./data/" + req.params.cls + "/train.txt", "a", 0644, function(err, file_handle) {
				if (!err) {
					fs.write(file_handle, req.body.text + LIM + req.body.label + os.EOL, null, 'utf8', function(err, written) {
						if (!err) {
							fs.close(file_handle);
							console.log('Данные добавлены в файл ' + req.params.cls + '/train.txt');
							res.send('ok');
						} else {
							console.log('Проблема при записи в файл ' + req.params.cls + '/train.txt', err);
							res.send('error');
						}
					});
				} else {
					console.log('Проблема при открытии файла ' + req.params.cls + '/train.txt', err);
					res.send('error');
				}
			});
		} else {
			console.log('Проблема при обращении к каталогу' + req.params.cls, err);
			res.send('error');
		}
	});
});

function Intersection(A, B){
	var M=A.length, N=B.length, C=[];
	for (var i=0; i<M; i++){ 
		var j=0, k=0;
		while (B[j]!==A[i] && j<N) j++;
		while (C[k]!==A[i] && k<C.length) k++;
		if (j!=N && k==C.length) C[C.length]=A[i];
	}
	return C;
}

function Difference(A, B){
	var M=A.length, N=B.length, C=[];
	for (var i=0; i<M; i++){ 
		var j=0, k=0;
		while (B[j]!==A[i] && j<N) j++;
		while (C[k]!==A[i] && k<C.length) k++;
		if (j==N && k==C.length) C[C.length]=A[i];
	}
	return C;
}

//фильтрует входящие данные по списку стоп-слов указанного класса
app.post('/stopwords/:cls', function(req, res){
    console.log('Фильтр по стоп-словам класса ' +  req.params.cls);
	console.log(req.body);
	var metaphone = natural.Metaphone,
		porterStemmer = natural.PorterStemmerRu,
		tokenizer = new natural.WordPunctTokenizer();
	
	var arr1 = tokenizer.tokenize(req.body.text);
	var arr2 = tokenizer.tokenize(req.body.text);
	console.log(arr1);
	for (var i = 0; i < arr2.length; i++){
		if (arr2[i].length > 1) {
			arr2[i] = porterStemmer.stem(arr2[i]);
			arr2[i] = transliteration(arr2[i]);
			arr2[i] = metaphone.process(arr2[i], Math.max(Math.round(arr2[i].length * 0.65), 3));
		}
	}
	console.log(arr2);
	var stopwords = [];
	var lineReader = require('readline').createInterface({
			terminal: false,
			input: fs.createReadStream("./data/stopwords.txt")
		});
	lineReader.on('line', function (line) {
		var stopword = porterStemmer.stem(line);
		stopword = transliteration(stopword);
		stopword = metaphone.process(stopword, Math.max(Math.round(stopword.length * 0.65), 3));
		stopwords.push(stopword);
	});
	lineReader.on('close', function () {
		arr2 = Difference(arr2, stopwords);
		console.log(arr2);
		var s = "";
		for (var i = 0; i < arr1.length; i++){
			var word = porterStemmer.stem(arr1[i]);
			word = transliteration(word);
			word = metaphone.process(word, Math.max(Math.round(word.length * 0.65), 3));
			var finded = false;
			for (var j = 0; j < arr2.length; j++){

				if (arr2[j] == word){
					finded = true;
					break;
				}
			}
			if (finded)
				if ((arr1[i].trim().length > 1) || (!isNaN(arr1[i].trim())))
					s = s + arr1[i].trim() + ' ';
		}
		console.log(s);
		res.send(s);
	});
	lineReader.on('error', function (err) {
		console.log('Проблема при работе с файлом ' + req.params.cls + '/stopwords.txt', err);
		lineReader.close();
		res.send('error');
	});
});

//тренирует сеть из файла обучения класса и сохраняет модель класса в файл
app.post('/train/:cls', function(req, res){
    console.log('Тренировка класса ' +  req.params.cls);
	var metaphone = natural.Metaphone,
		porterStemmer = natural.PorterStemmerRu,
		tokenizer = new natural.WordPunctTokenizer(),
		classifier = new natural.BayesClassifier();
	
	var stopwords = [];
	var lineReader1 = require('readline').createInterface({
		terminal: false,
		input: fs.createReadStream("./data/stopwords.txt")
	});
	lineReader1.on('line', function (line) {
		var stopword = porterStemmer.stem(line);
			stopword = transliteration(stopword);
			stopword = metaphone.process(stopword, Math.max(Math.round(stopword.length * 0.65), 3));
			stopwords.push(stopword);
	});
	lineReader1.on('error', function (err) {
		console.log('Проблема при работе с файлом stopwords.txt', err);
		lineReader.close();
		res.send('error');
	});
	
	var lineReader2 = require('readline').createInterface({
		terminal: false,
		input: fs.createReadStream("./data/" + req.params.cls + "/train.txt")
	});
	lineReader2.on('line', function (line) {
		var data = line.split(LIM);
		var arr = tokenizer.tokenize(data[0]);
		for (var i = 0; i < arr.length; i++) {
			arr[i] = porterStemmer.stem(arr[i]);
			arr[i] = transliteration(arr[i]);
			arr[i] = metaphone.process(arr[i], Math.max(Math.round(arr[i].length * 0.65), 3));
		}
		var Filtered = Difference(arr, stopwords);
		if (Filtered.length > 0){
			//console.log(arr, data[1]);
			console.log(Filtered, data[1]);
			classifier.addDocument(Filtered, data[1]);
		}
	});
	lineReader2.on('close', function () {
		classifier.train();
		classifier.save("./data/" + req.params.cls + "/net.json");
		console.log('Модель сохранена в файл ' + req.params.cls + '/net.json');
		res.send('ok');
	});
	lineReader2.on('error', function (err) {
		console.log('Проблема при работе с файлом ' + req.params.cls + '/train.txt', err);
		lineReader.close();
		res.send('error');
	});
});

//загружает сеть из файла модели класса и проверяет соответствуют классу
app.post('/check/:cls', function(req, res){
	console.log('Проверка соответствия классу ' + req.params.cls);
	console.log(req.body);
	var metaphone = natural.Metaphone,
		porterStemmer = natural.PorterStemmerRu,
		tokenizer = new natural.WordPunctTokenizer();
	natural.BayesClassifier.load("./data/" + req.params.cls + "/net.json", null, function(err, classifier) {
		if (!err) {
			var arr = tokenizer.tokenize(req.body.text);
			for (var i = 0; i < arr.length; i++){
				arr[i] = porterStemmer.stem(arr[i]);
				arr[i] = transliteration(arr[i]);
				arr[i] = metaphone.process(arr[i], Math.max(Math.round(arr[i].length * 0.65), 3));
			}
			var result = classifier.getClassifications(arr);
			console.log('Данные классифицированны');
			console.log(JSON.stringify(result));
			res.send(result);
		} else {
			console.log('Проблема при работе с файлом ' + req.params.cls + '/net.json', err);
			res.send('error');
		}
	});
});

app.listen(3000, function () {
    console.log('Сервер запущен');
});
