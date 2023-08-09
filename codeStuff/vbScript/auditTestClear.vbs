questionCnt = 1

For rowCnt = 4 To lastRow
    If objWorksheet.Range("G" & rowCnt).Interior.Color = RGB(238, 236, 225) Then
        mergeCnt = objWorksheet.Range("G" & rowCnt).MergeArea.Rows.Count - 1
        objWorksheet.Range("G" & rowCnt).Value = questionCnt
        questionCnt = questionCnt + 1
        rowCnt = rowCnt + mergeCnt
    End If
Next

' Sub auditTestClear
lastRow = objWorksheet.Cells(objWorksheet.Rows.Count, 1).End(-4162).Row
questionCnt = 1

For rowCnt = 4 To lastRow
    If objWorksheet.Range("D" & rowCnt).Interior.Color = RGB(255, 255, 0) Then
        objWorksheet.Range("D" & rowCnt).Value = ""
        questionCnt = questionCnt + 1
    End If
Next

questionCnt = 1

For rowCnt = 4 To lastRow
    If objWorksheet.Range("G" & rowCnt).Interior.Color = RGB(238, 236, 225) Then
        mergeCnt = objWorksheet.Range("G" & rowCnt).MergeArea.Rows.Count - 1
        objWorksheet.Range("G" & rowCnt).Value = ""
        questionCnt = questionCnt + 1
        rowCnt = rowCnt + mergeCnt
    End If
Next

objWorkbook.Close True
objExcel.Quit

Set objWorksheet = Nothing
Set objWorkbook = Nothing
Set objExcel = Nothing
