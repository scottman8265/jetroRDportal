Dim ExcelApp, Workbook, Worksheet, tblRng, rw, vLookup, fullRng
Dim hired, rowCnt, lRow, lCol, branch
Dim regional, region, bName

Set ExcelApp = CreateObject("Excel.Application")
Set Workbook = ExcelApp.Workbooks.Open("path_to_your_workbook.xlsx") ' Change this to your workbook's path
Set Worksheet = Workbook.Worksheets("Sheet1") ' Change this to your worksheet's name

lRow = Worksheet.Cells(Worksheet.Rows.Count, 1).End(-4162).Row
lCol = Worksheet.Cells(1, Worksheet.Columns.Count).End(-4159).Column
Set tblRng = Worksheet.Range("A2", Worksheet.Cells(lRow, lCol))
Set fullRng = Worksheet.Range("A1", Worksheet.Cells(lRow, lCol))
Set vLookup = ExcelApp.Workbooks("2020 Branch Breakout.xlsx").Worksheets("Branch vlookup tbl").Range("A2:H169")

rowCnt = 1

For Each rw In tblRng.Rows
    If rw.Row > 2 Then
        branch = Worksheet.Range("H" & rw.Row).Value

        If branch = 190 Or branch = 191 Then
            branch = 590
        End If

        region = ExcelApp.WorksheetFunction.vLookup(branch, vLookup, 7, False)
        bName = ExcelApp.WorksheetFunction.vLookup(branch, vLookup, 4, False)
        regional = ExcelApp.WorksheetFunction.vLookup(branch, vLookup, 5, False)

        Worksheet.Range("D" & rw.Row).Value = Worksheet.Range("D" & rw.Row).Value & " " & Worksheet.Range("E" & rw.Row).Value
        Worksheet.Range("G" & rw.Row).Value = region
        Worksheet.Range("I" & rw.Row).Value = bName
        Worksheet.Range("J" & rw.Row).Value = regional

        WScript.Echo rowCnt & " of " & (lRow - 1)
        rowCnt = rowCnt + 1
    End If
Next

Worksheet.Rows(1).EntireRow.Delete

Worksheet.Range("H:J").Cut
Worksheet.Range("A:A").Insert -4121
Worksheet.Range("G:G").Cut
Worksheet.Range("D:D").Insert -4121
Worksheet.Range("J:J").Cut
Worksheet.Range("D:D").Insert -4121
Worksheet.Range("G:G, I:J").Delete

Worksheet.Range("A:D, F:F, H:I").HorizontalAlignment = -4108

Workbook.Save
Workbook.Close
ExcelApp.Quit
Set ExcelApp = Nothing
