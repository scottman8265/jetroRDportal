Sub lcTestFormat()

'
' lcTestFormat Macro
'
'

    Dim lRow As Long
    Dim lCol As Long
    Dim wb As Workbook
    Dim rw, cell, col, scoreCell, idCell, evalCell As Range
    Dim copyData As Range
    Dim delRange As Range
    Dim fileName As String
    Dim fileName2 As String
    Dim harasNames As Variant
    Dim harasFile As Boolean

    harasNames = Array("regEng.csv", "mgrEng.csv", "regEsp.csv", "mgrEsp.csv", "nyEng.csv", "nyEsp.csv")
    Application.DisplayAlerts = False
    Application.ScreenUpdating = False
    For Each wb In Application.Workbooks
        harasFile = False
        If wb.Name <> "harassment.csv" And wb.Name <> "Sheet1.csv" And wb.Name <> "Report Server & Forms Format.XLSB" Then
            wb.Activate
            Debug.Print wb.Name
            lRow = Cells(Rows.Count, 1).End(xlUp).Row
            lCol = Cells(1, Columns.Count).End(xlToLeft).Column
            Set copyData = Range("A2", Cells(lRow, lCol))

            Set delRange = Rows(lRow + 1)

            'Debug.Print ("del range address: " + delRange.Address);

            For Each rw In copyData.Rows
                Set scoreCell = Cells(rw.Row, 8)
                Set evalCell = Cells(rw.Row, 9)

                If evalCell.Value = "No" Then
                    Set delRange = Union(delRange, Rows(rw.Row))
                ElseIf IsNumeric(Cells(rw.Row, 3)) = False Then
                    Set idCell = Cells(rw.Row, 3)
                    idCell.Value = Split(idCell.Value, "_")
                    If IsNumeric(idCell.Value) = False Then
                         Set delRange = Union(delRange, Rows(rw.Row))
                    End If
                End If
                extractDate rw.Row, 6
                extractDate rw.Row, 7
            Next rw

            If delRange Is Nothing Then
                Debug.Print wb.Name & ": delRange is empty"
            Else
                delRange.Delete
                Debug.Print (wb.Name & " completed")
            End If

            lRow = Cells(Rows.Count, 1).End(xlUp).Row
            lCol = Cells(1, Columns.Count).End(xlToLeft).Column
            Set copyData = Range("A1", Cells(lRow, lCol))

            copyData.Sort Key1:=Range("H2"), Order1:=xlDescending, Header:=xlNo
            copyData.Sort Key1:=Range("F2"), Order1:=xlDescending, Header:=xlNo
            copyData.Sort Key1:=Range("C2"), Order1:=xlDescending, Header:=xlNo

            copyData.RemoveDuplicates Columns:=3, Header:=xlYes

            Range("C2").NumberFormat = "0"

            For i = 0 To UBound(harasNames)
                'Debug.Print harasNames(i) + " - " + ActiveSheet.Name
                If ActiveWorkbook.Name = harasNames(i) Or wb.Name = "Report Server & Forms Format.XLSB" Then
                    harasFile = True
                    Exit For
                Else
                    harasFile = False
                End If
            Next i
            If harasFile = False Then
                wb.Save
                wb.Close
            End If
        End If
    Next wb
    combineSheetsOpenBooks.combineSheetsOpenBooks
    Application.DisplayAlerts = True
    Application.ScreenUpdating = True
End Sub

Sub extractDate(dateRow As Integer, dateCol As Integer)
    Dim dataCell As Range
    Set dataCell = Cells(dateRow, dateCol)
    dataCell = Split(dataCell, "-")
    dataCell = Trim(dataCell)
    dataCell.NumberFormat = "mm/dd/yyyy"
End Sub