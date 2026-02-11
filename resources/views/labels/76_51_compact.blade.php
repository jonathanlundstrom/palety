^XA

^FX Width of 76mm, 8 dots per mm.
^PW608

^FX Height of 51mm, 8 dots per mm.
^LL408

^FX Label header
^FO60,0^FB408,1,0,C,0^A0N,0,45^FWB^FD{{ $type }} {{ $id }}^FS

^FX Label content (QR-code)
^FO140,80
^BQN,2,12
^FDQA,{{ $data }}^FS

^FX Parcel/pallet weight
^FO440,64^GB120,300,5,B,0^FS
^FO465,0^FB408,1,0,C,0^A0N,0,20^FWB^FDWEIGHT^FS
^FO495,0^FB408,1,0,C,0^A0N,0,50^FWB^FD{{ $weight }} KG^FS

^XZ
