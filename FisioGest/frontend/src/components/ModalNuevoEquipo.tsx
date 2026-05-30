import React, { useState, ChangeEvent, FormEvent } from 'react';

interface FormDataType {
  nombre: string;
  tipo: string;
  marca: string;
  modelo: string;
  estado: 'disponible' | 'en_uso' | 'mantenimiento' | 'baja';
  cantidad: number;
  descripcion: string;
}

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  onGuardado: () => void;
}

interface ValidationErrors {
  [key: string]: string[];
}

const ModalNuevoEquipo: React.FC<ModalProps> = ({ isOpen, onClose, onGuardado }) => {
  const [formData, setFormData] = useState<FormDataType>({
    nombre: '',
    tipo: 'protesis', // Tus opciones de la línea 13: protesis, ortesis, maquina, otro
    marca: '',
    modelo: '',
    estado: 'disponible',
    cantidad: 1,
    descripcion: '',
  });

  const [errors, setErrors] = useState<ValidationErrors>({});
  const [loading, setLoading] = useState<boolean>(false);

  if (!isOpen) return null;

  const handleChange = (e: ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: name === 'cantidad' ? parseInt(value) || 0 : value,
    }));
    if (errors[name]) setErrors((prev) => ({ ...prev, [name]: undefined }));
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    const token = localStorage.getItem('user_token');

    try {
      const response = await fetch('http://127.0.0.1:8000/api/inventario', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (response.status === 422) {
        setErrors(result.errors || {});
      } else if (response.ok) {
        alert('¡Equipo agregado con éxito al inventario de FisioGest!');
        onGuardado();
        onClose();
      } else {
        alert(result.message || 'Error inesperado.');
      }
    } catch (error) {
      console.error(error);
      alert('Error de conexión con el servidor de Laravel.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
      <div className="bg-[#18181b] border border-gray-800 text-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        
        <div className="px-6 py-4 flex justify-between items-center border-b border-gray-800">
          <h3 className="text-lg font-semibold text-white">Añadir Nuevo Equipo</h3>
          <button type="button" onClick={onClose} className="text-gray-400 hover:text-white text-xl">&times;</button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          
          {/* Nombre */}
          <div>
            <label className="block text-sm font-medium text-gray-400 mb-1">Nombre del Equipo</label>
            <input
              type="text"
              name="nombre"
              value={formData.nombre}
              onChange={handleChange}
              placeholder="Ej. Prótesis de Rodilla A1"
              className={`w-full px-3 py-2 bg-[#27272a] border rounded-md text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 ${
                errors.nombre ? 'border-red-500' : 'border-gray-700'
              }`}
            />
            {errors.nombre && <p className="text-red-400 text-xs mt-1">{errors.nombre[0]}</p>}
          </div>

          {/* Fila: Marca y Modelo */}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-400 mb-1">Marca</label>
              <input
                type="text"
                name="marca"
                value={formData.marca}
                onChange={handleChange}
                placeholder="Marca"
                className="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-400 mb-1">Modelo</label>
              <input
                type="text"
                name="modelo"
                value={formData.modelo}
                onChange={handleChange}
                placeholder="Modelo"
                className="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>
          </div>

          {/* Tipo (Categoría de tu migración) */}
          <div>
            <label className="block text-sm font-medium text-gray-400 mb-1">Tipo de Item</label>
            <select
              name="tipo"
              value={formData.tipo}
              onChange={handleChange}
              className="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-sm"
            >
              <option value="protesis">Prótesis</option>
              <option value="ortesis">Órtesis</option>
              <option value="maquina">Máquina / Electroterapia</option>
              <option value="otro">Otro (Material Clínico)</option>
            </select>
          </div>

          {/* Fila: Cantidad y Estado */}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-400 mb-1">Cantidad</label>
              <input
                type="number"
                name="cantidad"
                min="1"
                value={formData.cantidad}
                onChange={handleChange}
                className="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-400 mb-1">Estado</label>
              <select
                name="estado"
                value={formData.estado}
                onChange={handleChange}
                className="w-full px-3 py-2 bg-[#27272a] border border-gray-700 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-sm"
              >
                <option value="disponible">Available (Disponible)</option>
                <option value="baja">Stock Bajo</option>
                <option value="en_uso">En Uso</option>
                <option value="mantenimiento">Mantenimiento</option>
              </select>
            </div>
          </div>

          {/* Botones de Acción */}
          <div className="flex space-x-3 pt-4">
            <button
              type="submit"
              disabled={loading}
              className="flex-1 py-2 bg-[#064e3b] hover:bg-[#047857] text-white font-medium rounded-md transition duration-200 disabled:opacity-50"
            >
              {loading ? 'Guardando...' : 'Guardar'}
            </button>
            <button
              type="button"
              onClick={onClose}
              className="flex-1 py-2 bg-[#27272a] hover:bg-[#3f3f46] text-gray-300 font-medium rounded-md transition duration-200"
            >
              Cancelar
            </button>
          </div>

        </form>
      </div>
    </div>
  );
};

export default ModalNuevoEquipo;