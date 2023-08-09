vbscript
formatLearningCenterTests.vb (1.5 KB) Raw
Dim oExcel, oWorkbook, oWorksheet
Set oExcel = CreateObject("Excel.Application")

Dim harasNames, harasFile, lRow, lCol, rw, dataCell
harasNames = Array("regEng.csv", "mgrEng.csv", "regEsp.csv", "mgrEsp.csv", "nyEng.csv", "nyEsp.csv")
harasFile = False

For Each oWorkbook In oExcel.Workbooks
    If oWorkbook.Name <> "harassment.csv" And oWorkbook.Name <> "Sheet1.csv" And oWorkbook.Name <> "Report Server & Forms Format.XLSB" Then
        Set oWorksheet = oWorkbook.Sheets(1)
        lRow = oWorksheet.Cells(oWorksheet.Rows.Count, 1).End(-4162).Row
        lCol = oWorksheet.Cells(1, oWorksheet.Columns.Count).End(-4159).Column

        ' Loop through rows and delete based on criteria
        For i = lRow To 2 Step -1
            If oWorksheet.Cells(i, 9).Value = "No" Or Not IsNumeric(oWorksheet.Cells(i, 3).Value) Then
                oWorksheet.Rows(i).Delete
            Else
                ' Extract date
                Set dataCell = oWorksheet.Cells(i, 6)
                dataCell.Value = Split(dataCell.Value, "-")(0)
                dataCell.NumberFormat = "mm/dd/yyyy"

                Set dataCell = oWorksheet.Cells(i, 7)
                dataCell.Value = Split(dataCell.Value, "-")(0)
                dataCell.NumberFormat = "mm/dd/yyyy"
            End If
        Next

        ' Sorting and other operations
        oWorksheet.Range("A1", oWorksheet.Cells(lRow, lCol)).Sort Key1:=oWorksheet.Range("H2"), Order1:=2, Header:=2
        oWorksheet.Range("A1", oWorksheet.Cells(lRow, lCol)).Sort Key1:=oWorksheet.Range("F2"), Order1:=2, Header:=2
        oWorksheet.Range("A1", oWorksheet.Cells(lRow, lCol)).Sort Key1:=oWorksheet.Range("C2"), Order1:=2, Header:=2

        oWorksheet.Range("A1", oWorksheet.Cells(lRow, lCol)).RemoveDuplicates Columns:=3, Header:=1
        oWorksheet.Range("C2").NumberFormat = "0"

        ' Check for harassment file
        For Each name In harasNames
            If oWorkbook.Name = name Then
                harasFile = True
                Exit For
            End If
        Next

        If harasFile = False Then
            oWorkbook.Save
            oWorkbook.Close
        End If

        ' Add code here to handle harassment file logic if needed
    End If
Next

oExcel.Quit
Set oExcel = Nothing
