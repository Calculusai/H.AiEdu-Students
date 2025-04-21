<?php
$page_title = '页面未找到';
include_once VIEW_PATH . '/header.php';
?>

<div class="container py-5 my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card glass animate-float shadow-lg border-0 rounded-lg overflow-hidden">
                <div class="card-body p-5 text-center">
                    <div class="error-number gradient-text display-1 animate-pulse fw-bold mb-4">404</div>
                    
                    <div class="avatar-circle bg-primary-soft mx-auto mb-4 animate-float-delay" style="width:120px;height:120px">
                        <i class="bi bi-search-heart display-3 text-primary"></i>
                    </div>
                    
                    <h2 class="gradient-text fw-bold mb-3">页面未找到</h2>
                    <p class="lead text-muted mb-5">您请求的页面不存在或已被移动。</p>
                    
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="javascript:history.back();" class="btn btn-outline-primary btn-lg btn-shine px-4 py-3 rounded-pill">
                            <i class="bi bi-arrow-left me-2"></i>返回上一页
                        </a>
                        <a href="<?php echo site_url(); ?>" class="btn btn-gradient btn-lg btn-shine px-4 py-3 rounded-pill">
                            <i class="bi bi-house-door me-2"></i>返回首页
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* 多巴胺风格动画效果 */
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
  100% { transform: translateY(0px); }
}

@keyframes pulse {
  0% { opacity: 0.8; }
  50% { opacity: 1; }
  100% { opacity: 0.8; }
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}

.animate-float-delay {
  animation: float 4s ease-in-out 1s infinite;
}

.animate-pulse {
  animation: pulse 2s ease-in-out infinite;
}

.error-number {
  font-size: 9rem;
  line-height: 1;
  text-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.gradient-text {
  background: linear-gradient(135deg, #6e57ff, #e942ff, #f64f59);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-size: 300% 300%;
  animation: gradient-shift 10s ease infinite;
}

@keyframes gradient-shift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.btn-gradient {
  background: linear-gradient(45deg, #6e57ff, #e942ff, #f64f59);
  border: none;
  color: white;
  background-size: 200% auto;
  transition: all 0.3s ease;
}

.btn-gradient:hover {
  background-position: right center;
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(110, 87, 255, 0.4);
}

.glass {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
}

.btn-shine {
  position: relative;
  overflow: hidden;
}

.btn-shine::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(
    to bottom right,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.1) 40%,
    rgba(255, 255, 255, 0.4) 50%,
    rgba(255, 255, 255, 0.1) 60%,
    rgba(255, 255, 255, 0) 100%
  );
  transform: rotate(45deg);
  animation: shine 3s infinite;
}

@keyframes shine {
  0% { left: -200%; }
  100% { left: 200%; }
}

.avatar-circle {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(110, 87, 255, 0.1);
}

.bg-primary-soft {
  background-color: rgba(110, 87, 255, 0.1);
}

.btn-outline-primary {
  border: 2px solid transparent;
  background-image: linear-gradient(white, white), linear-gradient(135deg, #6e57ff, #e942ff, #f64f59);
  background-origin: border-box;
  background-clip: padding-box, border-box;
  color: #6e57ff;
  transition: all 0.3s ease;
}

.btn-outline-primary:hover {
  border-color: transparent;
  color: white;
  background-image: linear-gradient(135deg, #6e57ff, #e942ff, #f64f59), 
                    linear-gradient(135deg, #6e57ff, #e942ff, #f64f59);
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(110, 87, 255, 0.4);
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 