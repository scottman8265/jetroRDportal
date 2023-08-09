
Option Explicit

Dim objExcel, objWorkbook, objWorksheet
Dim rowCnt, lastRow, questionCnt, mergeCnt

Set objExcel = CreateObject("Excel.Application")
objExcel.Visible = True 'Set to True to make Excel visible, False to keep it hidden
Set objWorkbook = objExcel.Workbooks.Open("Path\To\Your\Workbook.xlsx") 'Specify the workbook's path
Set objWorksheet = objWorkbook.Sheets("Sheet1") 'Specify the worksheet's name

' Sub auditTestFill
lastRow = objWorksheet.Cells(objWorksheet.Rows.Count, 1).End(-4162).Row
questionCnt = 1

For rowCnt = 4 To lastRow
    If objWorksheet.Range("D" & rowCnt).Interior.Color = RGB(255, 255, 0) Then
        objWorksheet.Range("D" & rowCnt).Value = "X"
        questionCnt = questionCnt + 1
    End If
Next

objWorkbook.Close True
objExcel.Quit

Set objWorksheet = Nothing
Set objWorkbook = Nothing
Set objExcel = Nothing
