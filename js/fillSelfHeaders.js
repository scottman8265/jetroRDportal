console.log('this is working');

function processFile(file) {
    console.log(typeof (file));
    sendData = {'file': file};
    console.log(sendData);
    $.ajax(
        {
            url: '../misc/fillSelfHeaders.php',
            type: 'POST',
            //data: {'file': file},
            contentType: false,
            success: function (res) {
                //document.write(res);
                console.log('success function obtained in fill self headers ajax');
            },
            error: function (e, f, g) {
                console.log(g);
            },
            complete: function (x) {
                console.log("complete function obtained");
            },
            //async: false
        });
}

    const fs = require(['fs'], function(fs) {});
    let newArr = new Array();
    fs.readdir('C:\\Users\\Scott\\Desktop\\2020 Q2 Self', (err, files) => {
        console.log(files.length);
        //newArr = files.map(processFile)
        processFile(files[0]);
    });
