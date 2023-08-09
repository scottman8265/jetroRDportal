Option Explicit

Dim objExcel, objWorkbook
Dim newSheet, sourceSheet
Dim lastRowSource, lastRowDest
Dim s

' Create Excel Application object
Set objExcel = CreateObject("Excel.Application")

' Make Excel visible (you can set this to False if you want it to run in the background)
objExcel.Visible = True

' Open the workbook you want to manipulate
Set objWorkbook = objExcel.Workbooks.Open("C:\path\to\your\file.xlsx")

' Add a new worksheet for combined data
Set newSheet = objWorkbook.Sheets.Add(After:=objWorkbook.Sheets(objWorkbook.Sheets.Count))
newSheet.Name = "Combined"

' Loop through each sheet
For s = 1 To objWorkbook.Sheets.Count - 1
    ' Skip the newly added combined sheet
    If objWorkbook.Sheets(s).Name <> "Combined" Then
        Set sourceSheet = objWorkbook.Sheets(s)

        ' Find last row of the source sheet
        lastRowSource = sourceSheet.Cells(sourceSheet.Rows.Count, 1).End(-4162).Row

        ' Find last row of the new combined sheet
        lastRowDest = newSheet.Cells(newSheet.Rows.Count, 1).End(-4162).Row + 1

        ' Copy data from source to destination
        sourceSheet.Range("A1:A" & lastRowSource).EntireRow.Copy Destination:=newSheet.Cells(lastRowDest, 1)
    End If
Next

' Clean up
Set newSheet = Nothing
Set sourceSheet = Nothing
Set objWorkbook = Nothing
objExcel.Quit
Set objExcel = Nothing
