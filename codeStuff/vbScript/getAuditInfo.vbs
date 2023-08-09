VBScript
Sub getResults()
    Dim year As Long
    Dim quarter As Long
    Dim branch As Long
    Dim fileSplit As Variant
    Dim findingRange As Range
    Dim repeatRange As Range
    Dim testCnt As Long
    Dim newWb As Object
    Dim recap As Object
    Dim wf As Object
    Dim lRepeatRow As Long
    Dim lFindingRow As Long
    Dim lRepeatSheetRow As Long
    Dim lFindingSheetRow As Long
    Dim lPeopleSheetRow As Long
    Dim stepper As Long
    Dim myPath As String
    Dim myFile As String
    Dim findingCnt As Long
    Dim repeatCnt As Long
    Dim branchCnt As Long
    Dim cellNum As Long
    Dim stepperStartRow As Long
    Dim peopleStartRow As Long
    Dim auditID As String

    Application.ScreenUpdating = False

    Set newWb = CreateObject("Excel.Workbook")
    newWb.Sheets.Add.Name = "findings"
    newWb.Sheets.Add.Name = "repeats"
    newWb.Sheets.Add.Name = "people"
    newWb.Sheets.Add.Name = "scores"

    nameNewWorkbookhdrs newWb

    myPath = "C:\off one drive\corp audits"
    If Right(myPath, 1) <> "\" Then myPath = myPath & "\"
    myFile = Dir(myPath & "*.xls")
    branchCnt = 0

    Do While Len(myFile) > 0
        findingCnt = 0
        repeatCnt = 0
        branchCnt = branchCnt + 1
        Set wf = CreateObject("Excel.Workbook")
        Set recap = wf.Sheets("OPS AUDIT RECAP")

        If recap.ProtectContents = True Then recap.Unprotect Password:="9713"

        fileSplit = Split(wf.Name, " ")
        year = fileSplit(0)
        quarter = Right(fileSplit(1), 1)
        If IsNumeric(fileSplit(3)) Then branch = fileSplit(3) Else branch = fileSplit(2)
        Debug.Print "[year: " & year & "][quarter: " & quarter & "][branch: " & branch & "]"
        auditID = year & quarter & branch & "corp"

        lFindingRow = recap.Cells(3, 2).End(xlDown).Row
        lRepeatRow = recap.Cells(Rows.Count, 2).End(xlUp).Row
        stepperStartRow = lRepeatRow - ((recap.Range("B" & lRepeatRow).Value - 1) * 8)
        Debug.Print "*****[findingRow: " & lFindingRow & "][lRepeatRow: " & lRepeatRow & "][stepperRow: " & stepperStartRow & "]"

        Set findingRange = recap.Range("C2:K" & lFindingRow)
        Set repeatRange = recap.Range("H" & stepperStartRow & ":AI" & lRepeatRow)
        Debug.Print "**********[findingRange: " & findingRange.Address & "][repeatRange: " & repeatRange.Address & "]"

        For Each rw In findingRange.Rows
            getFindingInfo rw.Row, newWb, wf, recap, findingCnt
            writeFindingInfo recap, newWb, rw.Row, auditID, testCnt
        Next rw

        writeRepeatInfo newWb, recap, stepperStartRow, auditID, lRepeatRow

        getPeopleInfo recap, newWb, stepperStartRow, auditID

        getScoreInfo recap, newWb, auditID

        wf.Close SaveChanges:=False
        myFile = Dir
        Debug.Print "***************[findings: " & findingCnt & "][repeats: " & repeatCnt & "]"
     Loop

     Debug.Print "total branches: " & branchCnt

    Application.ScreenUpdating = True

End Sub

Sub getFindingInfo(ByVal rw As Long, newWb As Object, wf As Object, recap As Worksheet, ByVal findingCnt As Integer)
    lFindingSheetRow = newWb.Sheets("findings").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Row
    If recap.Range("H" & rw) = "x" Or recap.Range("H" & rw) = "X" Then
        findingCnt = findingCnt + 1
        findChr33 = InStr(recap.Range("G" & rw).Formula, Chr(33))
        sheetName = Mid(recap.Range("G" & rw).Formula, 2, findChr33 - 2)
        cellNum = Mid(recap.Range("G" & rw).Formula, findChr33 + 2)

        If sheetName = Chr(39) & "PEST CONTROL" & Chr(39) Then sheetName = "Pest Control"
        If sheetName = Chr(39) & "WINE AND SPIRITS" & Chr(39) Then sheetName = "WINE AND SPIRITS"
        If sheetName = "FLOOR" And cellNum < 45 Then
            findChr33 = InStr(recap.Range("H" & rw).Formula, Chr(33))
            sheetName = Mid(recap.Range("H" & rw).Formula, 2, findChr33 - 2)
            cellNum = Mid(recap.Range("H" & rw).Formula, findChr33 + 2)
        End If

        awardedFormula = wf.Sheets(sheetName).Range("B" & cellNum)
        newWb.Sheets("findings").Cells(lFindingSheetRow, 11) = awardedFormula
    End If
End Sub

Sub writeFindingInfo(recap As Worksheet, newWb As Object, ByVal rw As Long, ByVal auditID As String, ByVal testCnt As Integer)
    If recap.Range("H" & rw) = "N" Or recap.Range("H" & rw) = "x" Or recap.Range("H" & rw) = "n" Or recap.Range("H" & rw) = "X" Then
        lFindingSheetRow = newWb.Sheets("findings").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Row
        testCnt = testCnt + 1
        newWb.Sheets("findings").Cells(lFindingSheetRow, 1) = auditID
        recap.Range("F" & rw & ":K" & rw).Copy
        newWb.Sheets("findings").Cells(lFindingSheetRow, 4).PasteSpecial xlPasteValues
    End If
End Sub

Sub writeRepeatInfo(newWb As Object, recap As Worksheet, ByVal stepperStartRow As Integer, ByVal auditID As String, ByVal lRepeatRow As Long)
    For stepper = stepperStartRow To lRepeatRow Step 8
        If recap.Range("H" & stepper).Offset(2, 0).Interior.Color <> vbWhite Then
            lRepeatSheetRow = newWb.Sheets("repeats").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Row
            repeatCnt = repeatCnt + 1
            newWb.Sheets("repeats").Cells(lRepeatSheetRow, 1) = auditID
            recap.Range("H" & stepper & ":AI" & stepper).Offset(1, 0).Copy
            newWb.Sheets("repeats").Cells(lRepeatSheetRow, 5).PasteSpecial xlPasteValues
            recap.Range("H" & stepper & ":AI" & stepper).Offset(2, 0).Copy
            newWb.Sheets("repeats").Cells(lRepeatSheetRow, 4).PasteSpecial xlPasteValues
        End If
    Next stepper
End Sub

Sub getPeopleInfo(recap As Worksheet, newWb As Workbook, ByVal stepperStartRow As Integer, ByVal auditID As String)

    Dim rowOffset As Integer
    Dim colOffset As Integer
    Dim colArray As Variant

    colArray = Array("P:V", "AC:AI", "AP:AT", "BA:BG")

    peopleStartRow = stepperStartRow - 27

    For colOffset = LBound(colArray) To UBound(colArray)
        For rowOffset = 0 To 7
            If recap.Range(Split(colArray(colOffset), ":")(1) & peopleStartRow + rowOffset) <> "" Or IsNumeric(recap.Range("V" & peopleStartRow + rowOffset)) = False Then
            lPeopleSheetRow = newWb.Sheets("people").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Row
                newWb.Sheets("people").Cells(lPeopleSheetRow, 1) = auditID
                newWb.Sheets("people").Cells(lPeopleSheetRow, 4) = recap.Range(Split(colArray(colOffset), ":")(0) & peopleStartRow + rowOffset)
                newWb.Sheets("people").Cells(lPeopleSheetRow, 5) = recap.Range(Split(colArray(colOffset), ":")(1) & peopleStartRow + rowOffset)
                Debug.Print "[dept: " & recap.Range(Split(colArray(colOffset), ":")(0) & peopleStartRow + 3) & "][person: " & recap.Range(Split(colArray(colOffset), ":")(1) & peopleStartRow + 3) & "]"
            End If
        Next rowOffset
    Next colOffset

End Sub

Sub writeFindingInfo(recap As Worksheet, newWb As Object, ByVal rw As Long, ByVal auditID As String, ByVal testCnt As Integer)
    If recap.Range("H" & rw) = "N" Or recap.Range("H" & rw) = "x" Or recap.Range("H" & rw) = "n" Or recap.Range("H" & rw) = "X" Then
        lFindingSheetRow = newWb.Sheets("findings").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Row
        testCnt = testCnt + 1
        newWb.Sheets("findings").Cells(lFindingSheetRow, 1) = auditID
        recap.Range("F" & rw & ":K" & rw).Copy
        newWb.Sheets("findings").Cells(lFindingSheetRow, 4).PasteSpecial xlPasteValues
    End If
End Sub

Sub writeRepeatInfo(newWb As Object, recap As Worksheet, ByVal stepperStartRow As Integer, ByVal auditID As String, ByVal lRepeatRow As Long)
    For stepper = stepperStartRow To lRepeatRow Step 8
        If recap.Range("H" & stepper).Offset(2, 0).Interior.Color <> vbWhite Then
            lRepeatSheetRow = newWb.Sheets("repeats").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Row
            newWb.Sheets("repeats").Cells(lRepeatSheetRow, 1) = auditID
            recap.Range("H" & stepper & ":AI" & stepper).Copy
            newWb.Sheets("repeats").Cells(lRepeatSheetRow, 5).PasteSpecial xlPasteValues
            recap.Range("H" & stepper & ":AI" & stepper).Offset(1, 0).Copy
            newWb.Sheets("repeats").Cells(lRepeatSheetRow, 4).PasteSpecial xlPasteValues
        End If
    Next stepper
End Sub
    Sub getScoreInfo(recap As Worksheet, newWb As Workbook, ByVal auditID As String)
    Dim scoreRange As Range

    Set scoreRange = recap.Range("H603:BK618")

    For Each rw In scoreRange.Rows
   ' Add a row for the total branch score
    lScoreSheetRow = newWb.Sheets("scores").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Row
    newWb.Sheets("scores").Cells(lScoreSheetRow, 1) = auditID
    newWb.Sheets("scores").Cells(lScoreSheetRow, 4) = "Total Branch"        'dept
    newWb.Sheets("scores").Cells(lScoreSheetRow, 5) = recap.Range("L595")   'repeatScore
    newWb.Sheets("scores").Cells(lScoreSheetRow, 6) = 0                     'findingCnt
    newWb.Sheets("scores").Cells(lScoreSheetRow, 7) = 0                     'repoeatCnt
    newWb.Sheets("scores").Cells(lScoreSheetRow, 8) = 0                     'naCnt
    newWb.Sheets("scores").Cells(lScoreSheetRow, 9) = recap.Range("H595")   'totalScore
    newWb.Sheets("scores").Cells(lScoreSheetRow, 10) = recap.Range("Z620")  'freshScore
    newWb.Sheets("scores").Cells(lScoreSheetRow, 11) = recap.Range("AH620") 'fsScore
    newWb.Sheets("scores").Cells(lScoreSheetRow, 12) = recap.Range("AX620") 'safeScore
    newWb.Sheets("scores").Cells(lScoreSheetRow, 13) = recap.Range("AP620") 'opsScore
    newWb.Sheets("scores").Cells(lScoreSheetRow, 14) = recap.Range("BF620") 'condScore
    newWb.Sheets("scores").Cells(lScoreSheetRow, 15) = ""                   'auditorID
    next rw
End Sub
