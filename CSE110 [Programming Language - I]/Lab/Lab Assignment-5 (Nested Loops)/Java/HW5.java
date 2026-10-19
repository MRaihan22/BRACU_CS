import java.util.Scanner;

public class HW5 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Input = ");
        int n = sc.nextInt();
        int count = 0;
        int num = 2;
        while (count < n) 
        {
            int sum = 0;
            for (int i = 1; i <= num/2; i++)
            {
                if (num % i == 0)
                {
                    sum += i;
                }
            }
            if (sum == num)
            {
                System.out.println("Output = " + num);
                count++;
            }
            num++;
        }
    }
}
