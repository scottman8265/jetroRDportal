$(document).ready(function () {

    console.log('ready');

    let fileCount = 0;
    let testCount = 0;
    let ajaxData;
    let tracking;
    let missedBranches = new Object();
    let allBranches = new Object();
    let namesArray = new Array();

    resetSession();

    setTaskContMargin();
    $('#rightColumn, #centerColumn .centerInfo, #leftColumn').load('inc/taskHTML/main.html')

    dialog('#uploadDialog', 500);
    dialog('#selectDialog', 650, 300);

    function setTaskContMargin() {

        let wholeWidth = $('#content-wrapper').width();
        let taskWidth = $('#task-container').width();
        let space = wholeWidth - taskWidth;
        let taskMargin = space / 2;

        $('#task-container').css('margin-left', taskMargin);

    }

    function resetSession() {
        $.post('verify/resetSession.php');
    }

    //sets dialog parameters width, height, auto open = false
    function dialog(selector, width, height = null) {
        console.log('inside dialog function');

        $(selector).dialog({
            width: width,
            autoOpen: false
        });

        if (height != null) {
            $(selector).dialog({height: height});
        }
    }

    //self explanatory
    function uploadPrevent(selector) {
        console.log('inside uploadPrevent function');

        $(selector).click(function (e) {
            e.preventDefault();
            console.log("clicked upload button");
        })
    }

    /*function uploadFile(sendData, count) {

        $.post('uploadFile.php', function (x) {
            console.log('after new upload file ' + x);
        });
    }*/

    function checkSession() {
        console.log('inside checkSession function');

        return $.ajax('verify/checkSession.php');
    }

    function verifySession(fileCount, fileType, whenRun, clicker, selector) {
        console.log('inside verifySession function');


        $('#' + fileType + '-status').html('Verifying Data');

        $.when(checkSession()).done(function (x) {

            if (+(x) === fileCount) {
                alert("Processing all " + fileCount + " files");
                doneWithFiles(fileType, selector);
            } else {
                alert("Processing " + x + " of " + fileCount + " files");
                doneWithFiles(fileType, selector);
            }
        });
    }

    /*function clearProcessedFiles(selector, fileCount, fileType, clicker) {

        sendData = new FormData();

        sendData.append('file', $(selector)[0].files);
        sendData.append('fileCount', fileCount);
        sendData.append('fileType', fileType);
        sendData.append('clicker', clicker);

        for (var pair of sendData.entries()) {
            console.log("sendData: " + pair[0] + ', ' + pair[1]);
        }

        $.ajax({
            type: 'POST',
            url: 'getSet/getProcessedFiles.php',
            data: sendData,
            //dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            success: function (x) {
                console.log(x);
            },
        });
    }*/

    function doneWithFiles(fileType, selector) {
        console.log('inside doneWithFiles function');
        console.log('fileType inside doneWithFiles function ' + $(selector) [0].files.length);
        let writeTo = null;
        if (fileType === 'branchCounts') {
            writeTo = 'cycleCounts';
            writeProcessedFiles();
        } else {
            writeTo = fileType;
        }

        $('#' + writeTo + '-status').html('Processing Data');

        if (fileType === 'inputFile') {
            fileType = 'branchCounts';
        }

        console.log('filetype inside done with files ' + fileType);

        $.post('process/' + fileType + '.php', {'inputFile': selector}, function (x) {
            $('#' + writeTo + '-output').html(x);
            $('#' + writeTo + '-status').html('Data Processed & Ready For Use');
        });
    }

    function uploadDialog(fileType) {
        console.log('inside uploadDialog function');

        $.post("dialogs/uploadDialog.php", {'fileType': fileType}, function (e) {

            $('.output').html('');
            $('.status').html('');

            $('#uploadDialog').html(e.html);

            $('#uploadDialog').dialog({title: e.title});
            $('#uploadDialog').dialog('open');

            uploadPrevent('#uploadFileButton');
            sendFile('#uploadFileButton', '#fileName', fileType);
        }, 'json');
    }

    function sendFile(clicker, selector, fileType) {
        console.log('inside sendFile function');

        $(clicker).click(function () {

            $('#uploadDialog').dialog('close');

            fileCount = $(selector)[0].files.length;

            console.log('fileCount inside sendFile: ' + fileCount);

            let sessionData;
            let fileData;
            if (fileCount > 0) {
                $('#' + fileType + '-status').html('Reading ' + fileCount + ' Files');
                for (let z = 0; z < fileCount; z++) {
                    sessionData = new FormData();
                    fileData = new FormData();
                    sessionData.append('fileType', fileType);
                    sessionData.append('count', (z + 1));
                    sessionData.append('fileCount', fileCount);
                    fileData.append('file', $(selector)[0].files[z]);
                    for (let pair of sessionData.entries()) {
                        console.log("sendData: " + pair[0] + ', ' + pair[1]);
                    }

                    $.ajax({
                        type: "POST",
                        url: "getSet/writeSessionData.php",
                        data: sessionData,
                        processData: false,
                        contentType: false,
                        done: function () {
                            console.log('finished writing session');
                        }
                    });

                    $.ajax({
                        type: 'POST',
                        url: 'uploadFile.php',
                        data: fileData,
                        contentType: false,
                        processData: false,
                        cache: false,
                        beforeSend: function () {
                            console.log('trying to make ajax call');
                            let data = fileData.get('file');
                            console.log('file name in get SendFIleData: ' + data.size);
                        },
                        success: function () {
                            console.log('file sent successfully');
                            if (count === fileCount) {
                                verifySession(fileCount, fileType, count + ' time', clicker, selector);
                            }
                        },
                        fail: function () {
                            console.log('file not sent');
                        }
                    })
                }
            }
        });
    }

    /*function getSendFileSendData(i, fileCount, selector, fileType) {
        console.log('inside getSendFileSendData function');

        sessionData = new FormData();
        fileData = new FormData();
        sessionData.append('fileType', fileType);
        sessionData.append('count', i);
        sessionData.append('fileCount', fileCount);
        fileData.append('file', $(selector)[0].files[i]);
        for (var pair of sessionData.entries()) {
            console.log("sendData: " + pair[0] + ', ' + pair[1]);
        }

        $.ajax({
            type: "POST",
            url: "getSet/writeSessionData.php",
            data: sessionData,
            processData: false,
            contentType: false
        });

        $.ajax({
            type: 'POST',
            url: 'uploadFile.php',
            data: fileData,
            dataType: 'json',
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function () {
                console.log('trying to make ajax call');
            },
            success: function () {
                console.log('file sent successfully');
            },
            fail: function () {
                console.log('file not sent');
            }

        });

        data = fileData.get('file');

        console.log('file name in get SendFIleData: ' + data.name);

        return fileData;
    }*/

    /*function chooseWkNum(fileType) {

        $.post('dialogs/ChooseWkDialog.php', {'fileType': fileType}, function (data) {
            $('#uploadDialog').dialog({title: 'Choose Week Num'});
            $('#uploadDialog').html(data);
            $('#uploadDialog').dialog('open');
        })

    }*/

    /*function varDumpSession() {
        $.post('verify/varDumpSession.php', function (x) {
            console.log(x);
        })
    }*/

    function writeProcessedFiles() {
        console.log('inside writeProcessedFiles function');

        $.post('process/writeProcessedFiles.php');
    }

    function chooseMonths(task) {
        console.log('inside chooseMonths function');

        $.post('dialogs/chooseMonthDialog.php', {'task': task}, function (x) {
            $('#selectDialog').dialog({title: 'Select Month(s)'});
            $('#selectWrap').html(x.html);
            $('#selectDialog').dialog('open');
        }, 'json');

    }

    function processTask(task) {
        console.log('inside processTask function');

        switch (task) {
            case 'updateMissingSelf':
                $.post('getSet/getMissingSelf.php', function (x) {
                });
                break;
            case 'selfScores':
                $.post('analyze/selfScores.php', function (x) {
                    writeSelfScores(x);
                }, 'json');
                break;
            case 'selfFailing':
                alert("Failings Not Available");
                break;
            case 'selfFindings':
                $.post('analyze/selfFindings.php', function () {
                    writeSelfFindings();
                });
                break;
            case 'selfMissing':
                alert("Missing NOt Available");
                break;
            default:
                alert("THERE'S AN ERROR SOMEWHERE");
                break;
        }
    }

    function writeSelfScores(x, count = 0) {
        console.log('inside writeSelfScores function');

        $('#self-status').html("");

        $('#self-status').html("Writing " + x[count] + ' Self Score Sheet');

        if (count !== x.length) {
            $.post('write/selfScores.php', {'type': x[count], 'count': count, 'x': x}, function (y) {
                let count = y.count;
                let x = y.x;

                count++;

                writeSelfScores(x, count);

            }, 'json')
                .fail(function (response) {
                    console.log(response.responseText);
                });
        } else {
            $('#self-status').html("Self Scores Ready to View");
        }
    }

    function writeSelfFindings() {
        console.log('inside writeSelfFindings function');

        $.post('write/selfFindings.php', function (x) {
        });
    }

    function closeUploadDialog() {
        console.log('inside closeUploadDialog function');

        if ($('#uploadDialog').dialog('isOpen') === true) {
            $('#uploadDialog').dialog('close');
        }
    }

    function moveInputFile(countType) {
        if (countType === 'inputFile') {
            $.post('getSet/getInputFile.php', function (x) {
                console.log('file name after getInputFile.php' + x.fileName);
            })
        }
    }

    function runFileNames(fileNames, fileType, wkNum) {

        //$("#audits-status").html('Processing Files');

        console.log('inside runFileNames');

        let fileCount = fileNames.length;
        let branch = [];
        let status = "";
        fileNames.forEach(function (x) {
            let split1 = x.split('/');
            let subString = split1[1].substring(8);
            let split2 = subString.split('.');
            branch.push(split2);
        });

        for (let i = 0; i < fileCount; i++) {
            status = 'Processing ' + branch[i] + " -  " + (i + 1) + " of " + fileCount;
            $("#" + fileType + "-info").html(status);
            console.log(status);

            let start = new Date().getTime();

            let sendData = {'fileType': fileType, 'wkNum': wkNum, 'files': fileNames[i]};

            console.log(fileNames[i]);

            $.ajax({
                type: 'POST',
                url: 'uploadFile.php',
                data: sendData,
                //async: false,
                success: function (xhrData) {
                    let end = new Date().getTime();
                    let executeTime = end - start;
                    testCount = testCount + 1;
                    console.log("files processed:  " + testCount);
                    console.log("execute time: " + executeTime);
                    console.log(xhrData);
                    let status = 'Processed ' + branch[i] + " -  " + (i + 1) + " of " + fileCount;
                    $("#" + fileType + "-info").html(status);
                },

            })
        }
        console.log("all Files Processed");
    }

    function getDateInfo() {

        $.ajax(
            {
                url: 'getSet/getDateInfo.php',
                type: 'POST',
                //async: false,
                success: function (res) {
                    console.log(res);
                    console.log(JSON.parse(res));
                    callback(res);
                }

            });
    }

    function processFormInfo(formInfo) {

        $.ajax({
            url: 'process/formInfo.php',
            type: 'POST',
            data: formInfo,
            //async: false,
            success: function (res) {
                callback(res);
            }

        });

    }

    function getTrackingInfo(tracking, date) {

        let sendData = {'tracking': tracking, 'date': date};
        console.log(sendData);
        $.ajax({
            url: 'getSet/getTrackingInfo.php',
            type: 'POST',
            data: sendData,
            //async: false,
            success: function (res) {
                console.log(res);
                callback(res);
            }

        })

    }

    function callback(data) {
        ajaxData = data;
    }


    $('#mainMenu').on('click', 'li', function () {
        let menuID = $(this).attr('id');
        $('.mainMenu span').removeClass('current');
        $("#" + menuID + ' span').addClass('current');
        let file = 'inc/taskHTML/' + menuID + '.html';
        let headerText = null;

        switch (menuID) {
            case 'main':
                headerText = "Log In \n [Not Functional]";
                break;
            case 'corpAudit':
                headerText = "Corp Audit Funcs \r [Partially Functional]";
                break;
            case 'selfAudit':
                headerText = 'Self Audit Funcs [Not Functional]';
                break;
            case 'cycleCount':
                headerText = 'Cycle Count Funcs [Not Functional]';
                break;
            case 'jcmsTesting':
                headerText = 'jcms Testing Funcs [Not Functional]';
                break;
            case 'learningCenter':
                headerText = "Learning Center Funcs [Not Functional]";
                break;
            case 'tracker':
                headerText = 'What Are You Tracking';
                break;
        }

        $('#header').text(headerText);
        $('#leftColumn').load(file);
    });

    $("#leftColumn").on("click", ".track", function () {

        getDateInfo();

        let dateData = JSON.parse(ajaxData);
        let formFile;
        let infoLine;
        let dateInfo;

        tracking = $(this).attr('id');

        switch (tracking) {
            case 'staffing':
                formFile = 'staffingForm.html';
                infoLine = 'Staffing Updates';
                dateInfo = dateData.wkEndDate;
                break;
            case 'cycleCount':
                formFile = 'cycleCountForm.html';
                infoLine = 'Cycle Counts';
                dateInfo = dateData.wkNum;
                break;
            case 'selfAudit':
                formFile = 'selfAuditForm.html';
                infoLine = 'Self Audits';
                dateInfo = dateData.wkEndDate;
                break;
            case 'kensKorner':
                formFile = 'kensKornerForm.html';
                infoLine = "Ken's Korner"
                dateInfo = dateData.kkDate;
                break;
            default:
                dateInfo = dateData.wkEndDate;
                break;
        }

        getTrackingInfo(tracking, dateInfo);

        let objects = JSON.parse(ajaxData);
        console.log(objects);
        missedBranches = objects['missed'];
        allBranches = objects['allBranches'];
        namesArray = objects['names'];

        console.log(missedBranches);
        console.log(allBranches);
        console.log(typeof (namesArray));

        $('#centerColumn .centerInfo').html("<h1>" + infoLine + "</h1>");
        $("#centerColumn .dateInfo").html("<h2>" + dateInfo + "</h2>");
        $("#centerColumn #form").load("inc/taskForms/" + formFile);
        $(".autocomplete").autocomplete({source: namesArray});
    });

    $("form").on('submit', function (e) {
        e.preventDefault();
        let formInfo = new Object();
        let date = $(".dateInfo").text();
        let key;
        let val;
        let objLength;

        console.log(typeof (formInfo));
        console.log($(".dateInfo h2").text());
        formInfo['date'] = date;
        formInfo['tracking'] = tracking;

        $(".input").each(function () {
            if ($(this).val()) {
                console.log($(this).attr('id'));
                key = $(this).attr('id');
                console.log($(this).val());
                val = $(this).val();
                formInfo[key] = val;
            }
        });

        objLength = Object.keys(formInfo).length;
        if (objLength > 1) {
            processFormInfo(formInfo);
            let returned = ajaxData;
            console.log(returned);
        }

        $('#form input').val("");
        $('#form .focus').focus();
    });

    $('#leftColumn').on('click', '.upload', function () {
        console.log('.tasks .upload clicked');

       getFiles('../input/auditsCorp', 'auditCorp');

        /*$.post('getSet/getDateInfo.php', function (y) {

            wkNum = y;
            console.log(wkNum);

            $.post("getSet/getFileNames.php", {'fileType': fileType}, function (x) {

                let fileNames = x.fileNames;
                let fileCount = fileNames.length;

                console.log("fileCount: " + fileCount);

                runFileNames(fileNames, fileType, wkNum);

            }, 'json')
        });*/
    });

    $('.tasks').on('click', '.counts', function () {
        console.log('.tasks .counts clicked');

        closeUploadDialog();

        let countType = $(this).attr('name');

        console.log('countType from clicking inputFile button: ' + countType);

        moveInputFile(countType);

        uploadDialog(countType);

    });

//click audit analyze button... get available periods... open period dialog box
    $('#audits-tasks').on('click', '.analyze', function () {
        console.log('#audits-tasks .analyze clicked');

        let task = $(this).data('task');

        $.post('getSet/setSession.php', {'task': task, 'reset': true}, function (data) {
        });

        //get available periods to choose from
        $.post('getSet/getSelectPeriods.php', function (data) {
            //get html to select periods in select dialog box
            $.post("dialogs/periodDialog.php", data, function (html) {

                $('#selectWrap').html(html);

                $('#selectDialog').dialog('open');

            });
        }, 'json')
    });

//flag missing self-audit status with '0' in auditAnalysis.selfaudits
    $('#self-tasks').on('click', '.update', function () {
        console.log('#self-tasks .update clicked');

        let task = $(this).data('task');
        chooseMonths(task);
    });

//open dialog to choose months for self audit analysis
    $('#self-tasks').on('click', '.analyze', function () {
        console.log('#self-tasks .analyze clicked');

        let task = $(this).data('task');
        chooseMonths(task);
    });

//select available audit periods... set audit IDs & available branches to session Variable...
    $('#selectWrap').on('click', '.selectPeriods', function () {
        $('#selectDialog').dialog('close');
        dialog('#selectDialog', 500);
        $("#selectDialog").dialog({title: "Select a Group"});

        let periods = $('#selectWrap input:checkbox:checked').map(function () {
            return $(this).attr('id')
        }).toArray();

        $.post('getSet/setSession.php', {'periods': periods}, function () {

            //gets group by dialog
            $.post('dialogs/groupByDialog.php', function (data) {
                $('#selectWrap').html(data);
                $('#selectDialog').dialog('open');
            });

        });

    });

//select available self-audit periods... set audit IDs & available branches to session Variable...
    $('#selectWrap').on('click', '.selectMonths', function () {
        $('#selectDialog').dialog('close');

        let periods = $('#selectWrap input:checkbox:checked').map(function () {
            return $(this).attr('id')
        }).toArray();

        let task = $(this).data('task');

        $.post('getSet/setSession.php', {'task': task, 'periods': periods, 'reset': true}, function () {
            processTask(task);
        });

    });

//choose group to view & opens view all or sort by dialog
    $('#selectWrap').on('click', '.selectGroup', function () {

        $('#selectDialog').dialog('close');
        //dialog("#selectDialog", 400, 300);
        //$('#selectDialog').dialog({title: 'Sort By/View Only'});
        let group = $('#selectWrap input:radio:checked').attr('id');

        $.post('getSet/setSession.php', {'group': group}, function (data) {
        });

    });

//change select dialog back to "Select Period(s) on close
    $('#selectDialog').on('dialogclose', function () {
        $('#selectDialog').dialog({title: 'Select Period(s)'});
        dialog("#selectDialog", 650, 300);
    });

    $('#uploadDialog').on('dialogclose', function () {
        $('#uploadDialog').dialog({title: 'Choose File(s) To Upload'});
        dialog("#uploadDialog", 500);
    });

    $('#uploadDialog').on('click', '.change', function () {
        let direction = $(this).data('change');
        let wkNum = $('#wkNum').text();

        if (direction === 'increase') {
            wkNum++;
        } else {
            wkNum--;
        }

        $.post('getSet/getWkEndDate.php', {'wkNum': wkNum}, function (data) {
            $('#wkEndDate').html('Wk End: ' + data);
        });

        $('#wkNum').text(wkNum);

    });

    $('#uploadDialog').on('click', '#chooseWk', function () {
        let wkNum = $('#wkNum').text();
        let fileType = $(this).data('filetype');
        $('#uploadDialog').dialog('close');

        $.post('getSet/setSession.php', {
            'reset': true,
            'wkNum': wkNum,
            'fileType': fileType,
            'update': false
        }, function () {

            uploadDialog(fileType);
        });

    });

    $('#uploadDialog').on('click', '#updateWk', function () {
        let wkNum = $('#wkNum').text();
        let fileType = $(this).data('fileType');
        $('#uploadDialog').dialog('close');

        $.post('getSet/setSession.php', {'wkNum': wkNum, 'fileType': fileType, 'update': true}, function () {
            uploadDialog(fileType);
        });

    });

    $(`.testBtn`).click(function () {
        $('#task-container').toggle();
        $('#data-container').toggle();
    })

});

let fileCount = 0;
let runningCount = 1;

function getFiles(dir, procFunc) {

    runningCount = 1;

    $.post('getSet/getFiles.php', {dir: dir}, function (e) {
        console.log(e);
        console.log(dir, procFunc);
        fileCount = e.length - 2;
        iterateFiles(e, 2, procFunc);
    }, 'json')

}

function iterateFiles(files, pointer, procFunc) {
    console.log("inside iterate file"    );
    if (files[pointer]) {
        switch (procFunc) {
            case 'aod':
                processAODfile(files[pointer], pointer, files, procFunc);
                break;
            case 'auditSelf':
                processSelfFile(files[pointer], pointer, files, procFunc);
                break;
            case 'auditCorp':
                processCorpFile(files[pointer], pointer, files, procFunc);
                break;
            case 'jcms':
                processJCMSfile(files[pointer], 2, 0, procFunc);
                break;
            case 'inv':
                processINVfile(files[pointer], pointer, files, procFunc);
                break;
        }
    } else {
        console.log('complete with files');
    }
}

function processCorpFile(file, pointer, files, procFunc) {
    console.log("inside process corp file");

    /*$.post('misc/fillCorpHeaders.php', {fileName: file}, function (e) {
        pointer++;
        console.log(runningCount + " of " + fileCount + " files");
        iterateFiles(files, pointer, procFunc);
        runningCount++;
    });*/

    $.post('processNewCorpAudit.php', {fileName: file}, function (e) {
        pointer++;
        console.log(runningCount + " of " + fileCount + " files");
        console.log(e);
        iterateFiles(files, pointer, procFunc);
        runningCount++;
    });
}

function processSelfFile(file, pointer, files, procFunc) {
    console.log(file);

    $.post('misc/fillSelfHeaders.php', {fileName: file}, function (e) {
        pointer++;
        console.log(runningCount + " of " + fileCount + " files");
        iterateFiles(files, pointer, procFunc);
        runningCount++;
    });

}

function processAODfile(file, pointer, files, procFunc) {
    console.log(file);

    $.post('parse/parseAODfile.php', {file: file}, function (e) {
        pointer++;
        console.log(runningCount + " of " + fileCount + " files");
        iterateFiles(files, pointer, procFunc);
        runningCount++;
    });
}

function processINVfile(file, pointer, files, procFunc) {
    console.log(file);

    $.post('inventories/importSummaries.php', {file: file}, function (e) {
        pointer++;
        console.log(runningCount + " of " + fileCount + " files");
        iterateFiles(files, pointer, procFunc);
        runningCount++;
    });
}

function processJCMSfile(file, filePointer, arrPointer, procFunc) {
    let tests = ['Admin Test ', 'Cashroom Test ', 'Deli Test ', 'Floor Test ', 'Front End Test ', 'Gen OPS Test ', 'IC Test', 'Meat Test ', 'Produce Test ', 'Receiving Test ', 'Reception Test ', 'Safety Test ', 'Smallwares Test ', 'Seafood Test '];

    if (tests[arrPointer]) {
        iterateTests(tests[arrPointer], arrPointer, file);
    } else {
        console.log('done with tests');
    }


}

function iterateTests(test, pointer, file) {
    $.post('jcms/importResults.php', {test: test, file: file}, function (e) {
        console.log(e);
        pointer++;
        processJCMSfile(file, 2, pointer, 'jcms');
    })
}

function testConnection() {
    console.log('Testing connection');
    $.post('misc/testConnection.php', function (e) {
        console.log(e);
        $('#infoFromDB').html(e);
    });
}