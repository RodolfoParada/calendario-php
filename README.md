```
USE calendario;
```


```
CREATE TABLE Tecnico (
    Id_Tecnico INT PRIMARY KEY AUTO_INCREMENT,
    rut INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    telefono VARCHAR(50),
    correo VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE
);
```
```
CREATE TABLE Cliente (
    Id_Cliente INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    direccion VARCHAR(50),
    telefono VARCHAR(50),
    correo VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE
);
```
```
CREATE TABLE Visita (
    Id_Visita INT PRIMARY KEY AUTO_INCREMENT,
    Id_Tecnico INT NOT NULL,
    Id_Cliente INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    estado ENUM('pendiente','realizada','cancelada') DEFAULT 'pendiente',

    FOREIGN KEY (Id_Tecnico) REFERENCES Tecnico(Id_Tecnico),
    FOREIGN KEY (Id_Cliente) REFERENCES Cliente(Id_Cliente)
);
```
```
INSERT INTO Tecnico (Id_Tecnico, rut, nombre, telefono, correo, activo) VALUES
(1, 12345678, 'Juan Pérez', '987654321', 'juan.perez@empresa.cl', TRUE),
(2, 87654321, 'María López', '912345678', 'maria.lopez@empresa.cl', TRUE),
(3, 11223344, 'Carlos Soto', '998877665', 'carlos.soto@empresa.cl', TRUE);
```
```
INSERT INTO Cliente (Id_Cliente, nombre, direccion, telefono, correo, activo) VALUES
(1, 'Constructora Andes', 'Av. Las Torres 1234', '225556677', 'contacto@andes.cl', TRUE),
(2, 'Servicios Patagonia', 'Calle Sur 567', '231231231', 'info@patagonia.cl', TRUE),
(3, 'Hotel Los Ríos', 'Camino Real 789', '224446600', 'reservas@losrios.cl', TRUE);
```
```
INSERT INTO Visita (Id_Visita, Id_Tecnico, Id_Cliente, fecha, hora, estado) VALUES
(1, 1, 1, '2025-03-10', '09:00:00', 'pendiente'),
(2, 1, 2, '2025-03-10', '11:30:00', 'realizada'),
(3, 2, 3, '2025-03-11', '15:00:00', 'pendiente'),
(4, 3, 1, '2025-03-12', '10:00:00', 'cancelada'),
(5, 2, 2, '2025-03-12', '08:30:00', 'pendiente');
```