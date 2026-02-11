^XA

^FX Width of 76mm, 8 dots per mm.
^PW608

^FX Height of 51mm, 8 dots per mm.
^LL408

^FX Label header
^FO0,50^FB420,1,0,C,0^A0N,0,45^FD{{ $type }} {{ $id }}^FS

^FX Label content (QR-code)
^FO55,108
^BQN,2,9
^FDQA,{{ $data }}^FS

^FX Parcel/pallet weight
^FO55,450^GB298,110,4,B,0^FS
^FO0,470^FB420,1,0,C,0^A0N,0,20^FDWEIGHT^FS
^FO0,500^FB420,1,0,C,0^A0N,0,50^FD{{ $weight }} KG^FS

^XZ
