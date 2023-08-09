Option Explicit

Dim objExcel, myPath, myFile, Wkb, Cnt, yr, qr, branch, branchSplit, auditType, startingRow
Dim objFSO, objFolder, objFile

Set objExcel = CreateObject("Excel.Application")
Set objFSO = CreateObject("Scripting.FileSystemObject")

objExcel.Visible = False
objExcel.DisplayAlerts = False

yr = InputBox("What is the year for reporting", "Year Entry", 2023)
qr = InputBox("What is the quarter for reporting? Number Only (1, 2, 3, 4)", "Quarter Entry")
auditType = InputBox("What is the audit type? corp/self")
startingRow = InputBox("What is the starting row for the branch manager name")

myPath = "C:\off one drive\" & auditType & " audits\"

If Right(myPath, 1) <> "\" Then myPath = myPath & "\"

Set objFolder = objFSO.GetFolder(myPath)

Cnt = 0

For Each objFile In objFolder.Files
    If Right(objFile.Name, 3) = "xls" Then
        Cnt = Cnt + 1
        branchSplit = Split(objFile.Name, " ")
        branch = branchSplit(3)
        Set Wkb = objExcel.Workbooks.Open(objFile.Path)
        If Wkb.Worksheets("OPS AUDIT RECAP").ProtectContents = True Then Wkb.Worksheets("OPS AUDIT RECAP").Unprotect Password:="9713"
        Wkb.Worksheets("OPS AUDIT RECAP").Range("V" & startingRow).Value = branch 'change the new value accordingly
        Wkb.Worksheets("OPS AUDIT RECAP").Range("V" & startingRow + 1).Value = yr 'change the new value accordingly
        Wkb.Worksheets("OPS AUDIT RECAP").Range("V" & startingRow + 2).Value = qr 'change the new value accordingly

        If auditType = "self" Then
            Wkb.Worksheets("OPS AUDIT RECAP").Range("V" & startingRow + 3).Value = "Self" 'change the new value accordingly only for self audits
        End If

        Wkb.Close True
        MsgBox (branch & " is complete")
    End If
Next

If Cnt > 0 Then
    MsgBox "Completed...", 48
Else
    MsgBox "No files were found!", 48
End If

objExcel.Quit

Set objExcel = Nothing
Set objFSO = Nothing
Set objFolder = Nothing
