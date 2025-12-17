import React, { useState, useRef } from 'react';
import { Wand2, Upload, Loader2, Download, Image as ImageIcon } from 'lucide-react';
import { editImageWithGemini } from '../services/geminiService';

interface ImageEditorProps {
  onImageSelected: (imageBase64: string) => void;
}

export const ImageEditor: React.FC<ImageEditorProps> = ({ onImageSelected }) => {
  const [originalImage, setOriginalImage] = useState<string | null>(null);
  const [generatedImage, setGeneratedImage] = useState<string | null>(null);
  const [prompt, setPrompt] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setOriginalImage(reader.result as string);
        setGeneratedImage(null); // Reset generated on new upload
      };
      reader.readAsDataURL(file);
    }
  };

  const handleGenerate = async () => {
    if (!originalImage || !prompt) return;

    setIsLoading(true);
    setError(null);

    try {
      // Extract base64 data and mime type
      const [mimeTypePrefix, base64Data] = originalImage.split(';base64,');
      const mimeType = mimeTypePrefix.split(':')[1];

      const result = await editImageWithGemini(base64Data, mimeType, prompt);
      setGeneratedImage(result);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Không thể tạo ảnh');
    } finally {
      setIsLoading(false);
    }
  };

  const handleUseImage = () => {
    if (generatedImage) {
      onImageSelected(generatedImage);
    } else if (originalImage) {
      onImageSelected(originalImage);
    }
  };

  return (
    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <div className="flex items-center gap-3 mb-6">
        <div className="p-2 bg-purple-100 rounded-lg">
          <Wand2 className="w-6 h-6 text-purple-600" />
        </div>
        <div>
          <h2 className="text-xl font-bold text-gray-900">Studio Thiết Kế AI</h2>
          <p className="text-sm text-gray-500">Tùy chỉnh nền trang thanh toán của bạn với Gemini AI</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Input Section */}
        <div className="space-y-6">
          {/* File Upload */}
          <div 
            className="border-2 border-dashed border-gray-200 rounded-xl p-8 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-gray-50 transition-colors"
            onClick={() => fileInputRef.current?.click()}
          >
            {originalImage ? (
               <img src={originalImage} alt="Original" className="max-h-48 rounded-lg object-contain" />
            ) : (
              <>
                <Upload className="w-10 h-10 text-gray-400 mb-3" />
                <p className="text-gray-600 font-medium">Nhấn để tải ảnh lên</p>
                <p className="text-sm text-gray-400">Hỗ trợ JPG, PNG</p>
              </>
            )}
            <input 
              type="file" 
              ref={fileInputRef} 
              className="hidden" 
              accept="image/*"
              onChange={handleFileChange}
            />
          </div>

          {/* Prompt Input */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Bạn muốn chỉnh sửa ảnh này như thế nào?
            </label>
            <textarea
              className="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-shadow"
              placeholder="Ví dụ: Thêm bộ lọc cổ điển, phong cách cyberpunk, thêm trang trí lễ hội, làm cho ảnh rực rỡ hơn..."
              rows={3}
              value={prompt}
              onChange={(e) => setPrompt(e.target.value)}
            />
          </div>

          {error && (
            <div className="p-3 bg-red-50 text-red-700 rounded-lg text-sm">
              {error}
            </div>
          )}

          <button
            onClick={handleGenerate}
            disabled={!originalImage || !prompt || isLoading}
            className="w-full bg-purple-600 text-white font-semibold py-3 rounded-xl hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            {isLoading ? (
              <>
                <Loader2 className="w-5 h-5 animate-spin" />
                Đang xử lý với Gemini...
              </>
            ) : (
              <>
                <Wand2 className="w-5 h-5" />
                Tạo Biến Thể
              </>
            )}
          </button>
        </div>

        {/* Output Section */}
        <div className="bg-gray-50 rounded-xl p-6 flex flex-col items-center justify-center border border-gray-100 min-h-[300px]">
          {generatedImage ? (
            <div className="space-y-4 w-full">
               <div className="relative group">
                <img 
                  src={generatedImage} 
                  alt="AI Generated" 
                  className="w-full rounded-lg shadow-md"
                />
               </div>
               <div className="flex gap-3">
                 <button 
                  onClick={handleUseImage}
                  className="flex-1 bg-green-600 text-white py-2 rounded-lg font-medium hover:bg-green-700 transition-colors"
                 >
                   Sử Dụng Ảnh Này
                 </button>
                 <a 
                   href={generatedImage} 
                   download="gemini-edit.png"
                   className="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-700"
                 >
                   <Download className="w-5 h-5" />
                 </a>
               </div>
            </div>
          ) : (
            <div className="text-center text-gray-400">
              <ImageIcon className="w-12 h-12 mx-auto mb-3 opacity-20" />
              <p>Ảnh được tạo sẽ xuất hiện ở đây</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};